<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorException;
use Helmich\Schema2Class\Generator\SchemaToClass;

class IntersectProperty extends AbstractPropertyInterface
{
    use TypeConvert;

    public static function canHandleSchema(array $schema): bool
    {
        return isset($schema["allOf"]);
    }

    public function isComplex(): bool
    {
        return true;
    }

    public function convertJSONToType(string $inputVarName = 'input'): string
    {
        $key = $this->key;

        return "\$$key = {$this->subTypeName()}::buildFromInput(\${$inputVarName}['$key']);";
    }

    public function convertTypeToJSON(string $outputVarName = 'output'): string
    {
        $key = $this->key;

        return "\${$outputVarName}['$key'] = \$this->{$key}->toJson();";
    }

    public function cloneProperty(): string
    {
        $key = $this->key;

        return "\$this->$key = clone \$this->$key;";
    }

    /**
     * @param SchemaToClass    $generator
     * @throws GeneratorException
     */
    public function generateSubTypes(SchemaToClass $generator): void
    {
        $propertyTypeName = $this->subTypeName();
        $combined = $this->buildSchemaIntersect($this->schema["allOf"]);

        $generator->schemaToClass(
            $this->generatorRequest
                ->withSchema($combined)
                ->withClass($propertyTypeName)
        );
    }

    public function typeAnnotation(): string
    {
        return $this->subTypeName();
    }

    public function typeHint(string $phpVersion): string
    {
        return "\\" . $this->generatorRequest->getTargetNamespace() . "\\" . $this->subTypeName();
    }

    private function subTypeName(): string
    {
        return $this->generatorRequest->getTargetClass() . $this->capitalizedName;
    }

    private function buildSchemaUnion(array $schemas): array
    {
        $combined = [
            "required" => [],
            "properties" => [],
        ];

        foreach ($schemas as $i => $schema) {
            $required = isset($schema["required"]) ? $schema["required"] : [];

            if ($i === 0) {
                $combined["required"] = $required;
            } else {
                foreach ($combined["required"] as $j => $req) {
                    if (!in_array($req, $required)) {
                        unset($combined["required"][$j]);
                    }
                }
            }

            if (isset($schema["properties"])) {
                foreach ($schema["properties"] as $name => $def) {
                    $combined["properties"][$name] = $def;
                }
            }
        }

        return $combined;
    }

    private function buildSchemaIntersect(array $schemas): array
    {
        $combined = [
            "required" => [],
            "properties" => [],
        ];

        foreach ($schemas as $schema) {
            if (isset($schema["oneOf"])) {
                $schema = $this->buildSchemaUnion($schema["oneOf"]);
            }

            if (isset($schema["anyOf"])) {
                $schema = $this->buildSchemaUnion($schema["anyOf"]);
            }

            if (isset($schema["required"])) {
                $combined["required"] = array_unique(array_merge($combined["required"], $schema["required"]));
            }

            if (isset($schema["properties"])) {
                foreach ($schema["properties"] as $name => $def) {
                    $combined["properties"][$name] = $def;
                }
            }
        }

        return $combined;
    }

}
