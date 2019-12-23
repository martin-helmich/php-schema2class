<?php
declare(strict_types=1);

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorContext;
use Helmich\Schema2Class\Generator\GeneratorException;
use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\PropertyBuilder;
use Helmich\Schema2Class\Generator\SchemaToClass;

class UnionProperty extends AbstractProperty
{
    use TypeConvert;

    /** @var PropertyInterface[] */
    private array $subProperties;

    public function __construct(string $key, array $schema, GeneratorRequest $generatorRequest)
    {
        if (isset($schema["anyOf"])) {
            $schema["oneOf"] = $schema["anyOf"];
            unset($schema["anyOf"]);
        }

        $subSchemas = $schema["oneOf"];

        $this->subProperties = array_map(function (int $idx) use ($generatorRequest, $key, $subSchemas): PropertyInterface {
            $subSchema = $subSchemas[$idx];
            return PropertyBuilder::buildPropertyFromSchema($generatorRequest, "{$key}Alternative" . ($idx + 1), $subSchema, true);
        }, array_keys($schema["oneOf"]));

        parent::__construct($key, $schema, $generatorRequest);
    }

    public static function canHandleSchema(array $schema): bool
    {
        return isset($schema["oneOf"]) || isset($schema["anyOf"]);
    }

    public function isComplex(): bool
    {
        return true;
    }

    public function convertJSONToType(string $inputVarName = 'input'): string
    {
        $key    = $this->key;
        $keyStr = var_export($key, true);

        $conversions = ["\$$key = \${$inputVarName}[{$keyStr}];" => ["discriminators" => [], "fallback" => true]];

        foreach ($this->subProperties as $i => $subProp) {
            $mapping       = $subProp->generateInputMappingExpr("\${$inputVarName}[{$keyStr}]");
            $assignment    = "\$$key = {$mapping};";
            $discriminator = $subProp->generateInputAssertionExpr("\${$inputVarName}[{$keyStr}]");

            if (!isset($conversions[$assignment])) {
                $conversions[$assignment] = ["discriminators" => [], "fallback" => false];
            }

            $conversions[$assignment]["discriminators"][] = $discriminator;
        }

        $ifs      = 0;
        $branches = [];
        $fallback = null;
        foreach ($conversions as $assignment => $conversion) {
            if ($conversion["fallback"]) {
                $fallback = $assignment;
                continue;
            }
            $condition  = "(" . join(") || (", $conversion["discriminators"]) . ")";
            $branches[] = ($ifs++ > 0 ? "else " : "") . "if ($condition) {\n    $assignment\n}";
        }

        if ($fallback) {
            if (count($branches) > 0) {
                $branches[] = "else {\n    $fallback\n}";
            } else {
                $branches[] = $fallback;
            }
        }

        return str_replace("}\nelse", "} else", join("\n", $branches));
    }

    public function convertTypeToJSON(string $outputVarName = 'output'): string
    {
        $key         = $this->key;
        $keyStr      = var_export($key, true);
        $conversions = [];

        foreach ($this->subProperties as $subProperty) {
            $mapping       = $subProperty->generateOutputMappingExpr("\$this->{$key}");
            $assignment    = "\${$outputVarName}[{$keyStr}] = {$mapping};";
            $discriminator = $subProperty->generateTypeAssertionExpr("\$this->{$key}");

            if (!isset($conversions[$assignment])) {
                $conversions[$assignment] = ["discriminators" => []];
            }

            $conversions[$assignment]["discriminators"][] = $discriminator;
        }

        $ifs      = 0;
        $branches = [];
        $fallback = null;
        foreach ($conversions as $assignment => $conversion) {
            $condition  = "(" . join(") || (", $conversion["discriminators"]) . ")";
            $branches[] = ($ifs++ > 0 ? "else " : "") . "if ($condition) {\n    $assignment\n}";
        }

        return str_replace("}\nelse", "} else", join("\n", $branches));
    }

    /**
     * @param SchemaToClass $generator
     * @throws GeneratorException
     */
    public function generateSubTypes(SchemaToClass $generator): void
    {
        $def = $this->schema;

        foreach ($def["oneOf"] as $i => $subDef) {
            $propertyTypeName = $this->subTypeName($i);

            if ((isset($subDef["type"]) && $subDef["type"] === "object") || isset($subDef["properties"])) {
                $generator->schemaToClass(
                    $this->generatorRequest
                        ->withSchema($subDef)
                        ->withClass($propertyTypeName)
                );
            }
        }
    }

    public function typeAnnotation(): string
    {
        $types = [];
        $def   = $this->schema;

        foreach ($def["oneOf"] as $i => $subDef) {
            $propertyTypeName = $this->subTypeName($i);
            if ((isset($subDef["type"]) && $subDef["type"] === "object") || isset($subDef["properties"])) {
                $types[] = $propertyTypeName;
            } else {
                $types[] = $this->phpPrimitiveForSchemaType($subDef)[0];
            }
        }

        return join("|", $types);
    }

    public function typeHint(string $phpVersion)
    {
        return null;
    }

    public function generateTypeAssertionExpr(string $expr): string
    {
        $subAssertions = [];

        foreach ($this->subProperties as $prop) {
            $subAssertions[] = $prop->generateTypeAssertionExpr($expr);
        }

        return "(" . join(") || (", $subAssertions) . ")";
    }

    public function generateInputAssertionExpr(string $expr): string
    {
        $subAssertions = [];

        foreach ($this->subProperties as $prop) {
            $subAssertions[] = $prop->generateInputAssertionExpr($expr);
        }

        return "(" . join(") || (", $subAssertions) . ")";
    }

    public function generateInputMappingExpr(string $expr): string
    {
        $out = "null";

        foreach ($this->subProperties as $i => $subProperty) {
            $assert = $subProperty->generateInputAssertionExpr($expr);
            $map    = $subProperty->generateInputMappingExpr($expr);
            $out    = "({$assert}) ? ({$map}) : ({$out})";
        }

        return $out;
    }

    public function generateOutputMappingExpr(string $expr): string
    {
        $out = "null";

        foreach ($this->subProperties as $i => $subProperty) {
            $assert = $subProperty->generateTypeAssertionExpr($expr);
            $map    = $subProperty->generateOutputMappingExpr($expr);
            $out    = "({$assert}) ? ({$map}) : ({$out})";
        }

        return $out;
    }

    public function generateCloneExpr(string $expr): string
    {
        $out = $expr;

        foreach ($this->subProperties as $i => $subProperty) {
            $assert = $subProperty->generateTypeAssertionExpr($expr);
            $map    = $subProperty->generateCloneExpr($expr);
            $out    = "({$assert}) ? ({$map}) : ({$out})";
        }

        return $out;
    }

    private function subTypeName(int $idx = 0): string
    {
        return $this->generatorRequest->getTargetClass() . $this->capitalizedName . "Alternative" . ($idx + 1);
    }

}
