<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorException;
use Helmich\Schema2Class\Generator\SchemaToClass;

class NestedObjectProperty extends AbstractPropertyInterface
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

    private function subTypeName(): string
    {
        return $this->generatorRequest->getTargetClass() . $this->capitalizedName;
    }

}
