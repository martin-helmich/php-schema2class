<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorException;
use Helmich\Schema2Class\Generator\SchemaToClass;

class ObjectArrayProperty extends AbstractPropertyInterface
{
    use TypeConvert;

    public static function canHandleSchema(array $schema): bool
    {
        $isArray = isset($schema["type"]) && $schema["type"] === "array";
        if (!$isArray) {
            return false;
        }

        return (isset($schema["items"]["type"]) && $schema["items"]["type"] === "object") || isset($schema["items"]["properties"]);
    }

    public function isComplex(): bool
    {
        return true;
    }

    public function convertJSONToType(string $inputVarName = 'input'): string
    {
        $key = $this->key;
        return "\$$key = " . 'array_map(function($i) { return ' . $this->subTypeName() . "::buildFromInput(\$i); }, \${$inputVarName}['$key']);";
    }

    public function convertTypeToJSON(string $outputVarName = 'output'): string
    {
        $key = $this->key;
        $st = $this->subTypeName();

        return "\${$outputVarName}['$key'] = array_map(function($st \$i) { return \$i->toJson(); }, \$this->$key);";
    }

    public function cloneProperty(): string
    {
        $key = $this->key;
        $st = $this->subTypeName();

        return "\$this->$key = array_map(function($st \$i) { return clone \$i; }, \$this->$key);";
    }

    /**
     * @param SchemaToClass    $generator
     * @throws GeneratorException
     */
    public function generateSubTypes(SchemaToClass $generator): void
    {
        $def = $this->schema;

        $generator->schemaToClass(
            $this->generatorRequest->withSchema($def["items"])->withClass($this->subTypeName())
        );
    }

    public function typeAnnotation(): string
    {
        return $this->subTypeName() . "[]";
    }

    public function typeHint(string $phpVersion): string
    {
        return "array";
    }

    public function assertion(string $expr): string
    {
        $st = $this->subTypeName();
        return "is_array({$expr}) && count(array_filter({$expr}, function({$st} \$item) {return \$item instanceof {$st};})) === count({$expr})";
    }

    private function subTypeName(): string
    {
        return $this->generatorRequest->getTargetClass() . $this->capitalizedName . 'Item';
    }

}
