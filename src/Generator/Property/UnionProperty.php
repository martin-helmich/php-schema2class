<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorContext;
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

        $this->subProperties = array_map(function(int $idx) use ($generatorRequest, $key, $subSchemas): PropertyInterface {
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
        $def = $this->schema;
        $key = $this->key;
        $keyStr = var_export($key, true);

        $conversions = ["\$$key = \${$inputVarName}[{$keyStr}];" => ["discriminators" => [], "fallback" => true]];

        foreach($this->subProperties as $i => $subProp) {
            $propertyTypeName = $this->subTypeName($i);
            $mapping = $subProp->mapFromInput("\${$inputVarName}[{$keyStr}]");
            $assignment = "\$$key = {$mapping};";
            $discriminator = $subProp->inputAssertion("\${$inputVarName}[{$keyStr}]");

//            if ((isset($subDef["type"]) && $subDef["type"] === "object") || isset($subDef["properties"])) {
//                $assignment = "\$$key = $propertyTypeName::buildFromInput(\${$inputVarName}['$key']);";
////                $discriminator = "$propertyTypeName::validateInput(\${$inputVarName}['$key'], true)";
//            } else if ($subDef["type"] === "array") {
//                // TODO
//            } else if ($subDef["type"] === "int" || $subDef["type"] === "integer") {
////                $discriminator = "is_int(\${$inputVarName}['$key'])";
//            } else if ($subDef["type"] === "string") {
////                $discriminator = "is_string(\${$inputVarName}['$key'])";
//            }

            if (!isset($conversions[$assignment])) {
                $conversions[$assignment] = ["discriminators" => [], "fallback" => false];
            }

            $conversions[$assignment]["discriminators"][] = $discriminator;
        }

        $ifs = 0;
        $branches = [];
        $fallback = null;
        foreach ($conversions as $assignment => $conversion) {
            if ($conversion["fallback"]) {
                $fallback = $assignment;
                continue;
            }
            $condition = "(" . join(") || (", $conversion["discriminators"]) . ")";
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
        $def = $this->schema;
        $key = $this->key;
        $conversions = [];

        foreach ($def["oneOf"] as $i => $subDef) {
            $propertyTypeName = $this->subTypeName($i);
            $assignment = "\${$outputVarName}['$key'] = \$this->{$key};";
            $discriminator = "true";

            if ((isset($subDef["type"]) && $subDef["type"] === "object") || isset($subDef["properties"])) {
                $assignment = "\${$outputVarName}['$key'] = \$this->{$key}->toJson();";
                $discriminator = "\$this->{$key} instanceof ${propertyTypeName}";
            } else if ($subDef["type"] === "array") {
                // TODO
            } else if ($subDef["type"] === "int" || $subDef["type"] === "integer") {
                $discriminator = "is_int(\$this->{$key})";
            } else if ($subDef["type"] === "string") {
                $discriminator = "is_string(\$this->{$key})";
            }

            if (!isset($conversions[$assignment])) {
                $conversions[$assignment] = ["discriminators" => []];
            }

            $conversions[$assignment]["discriminators"][] = $discriminator;
        }

        $ifs = 0;
        $branches = [];
        $fallback = null;
        foreach ($conversions as $assignment => $conversion) {
            $condition = "(" . join(") || (", $conversion["discriminators"]) . ")";
            $branches[] = ($ifs++ > 0 ? "else " : "") . "if ($condition) {\n    $assignment\n}";
        }

        return str_replace("}\nelse", "} else", join("\n", $branches));
    }

    public function cloneProperty(): string
    {
        $key = $this->key;

        return "\$this->$key = clone \$this->$key;";
    }

    /**
     * @param SchemaToClass    $generator
     * @throws \Helmich\Schema2Class\Generator\GeneratorException
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
        $def = $this->schema;

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

    public function assertion(string $expr): string
    {
        $subAssertions = [];

        foreach($this->subProperties as $prop) {
            $subAssertions[] = $prop->assertion($expr);
        }

        return "(" . join(") || (", $subAssertions) . ")";
    }

    public function inputAssertion(string $expr): string
    {
        $subAssertions = [];

        foreach($this->subProperties as $prop) {
            $subAssertions[] = $prop->inputAssertion($expr);
        }

        return "(" . join(") || (", $subAssertions) . ")";
    }

    public function mapFromInput(string $expr): string
    {
        $out = "null";

        foreach ($this->subProperties as $i => $subProperty) {
            $assert = $subProperty->inputAssertion($expr);
            $map = $subProperty->mapFromInput($expr);
            $out = "({$assert}) ? ({$map}) : ({$out})";
        }

        return $out;
    }

    private function subTypeName(int $idx = 0): string
    {
        return $this->generatorRequest->getTargetClass() . $this->capitalizedName . "Alternative" . ($idx + 1);
    }

}
