<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorException;
use Helmich\Schema2Class\Generator\SchemaToClass;

class NestedObjectProperty extends AbstractProperty
{
    use TypeConvert;

    public static function canHandleSchema(array $schema): bool
    {
        return isset($schema["type"]) && $schema["type"] === "object"
            || isset($schema["properties"]);
    }

    public function isComplex(): bool
    {
        return true;
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
        $generator->schemaToClass(
            $this->generatorRequest
                ->withSchema($this->schema)
                ->withClass($this->subTypeName())
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

    public function assertion(string $expr): string
    {
        return "{$expr} instanceof {$this->subTypeName()}";
    }

    public function inputAssertion(string $expr): string
    {
        return "{$this->subTypeName()}::validateInput({$expr}, true)";
    }

    public function mapFromInput(string $expr): string
    {
        return "{$this->subTypeName()}::buildFromInput({$expr})";
    }

    private function subTypeName(): string
    {
        return $this->generatorRequest->getTargetClass() . $this->capitalizedName;
    }

}
