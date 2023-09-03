<?php
declare(strict_types=1);

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorException;
use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\PropertyBuilder;
use Helmich\Schema2Class\Generator\SchemaToClass;

class ObjectArrayProperty extends AbstractProperty
{
    use TypeConvert;

    private PropertyInterface $itemType;
    private array $itemSchema;

    /**
     * ObjectArrayProperty constructor.
     * @param string $key
     * @param array $schema
     * @param GeneratorRequest $generatorRequest
     */
    public function __construct(string $key, array $schema, GeneratorRequest $generatorRequest)
    {
        $this->itemSchema = $schema["additionalProperties"] ?? $schema["items"];

        $this->itemType = PropertyBuilder::buildPropertyFromSchema($generatorRequest, $key . "Item", $this->itemSchema, true);
        parent::__construct($key, $schema, $generatorRequest);
    }


    public static function canHandleSchema(array $schema): bool
    {
        $itemSchema         = null;
        $isAssociativeArray = isset($schema["additionalProperties"]);
        $isArray            = isset($schema["type"]) && $schema["type"] === "array";

        if ($isAssociativeArray) {
            $itemSchema = $schema["additionalProperties"];
        }

        if ($isArray) {
            $itemSchema = $schema["items"];
        }

        if (!$isArray && !$isAssociativeArray) {
            return false;
        }

        return (isset($itemSchema["type"]) && $itemSchema["type"] === "object") || isset($itemSchema["properties"]);
    }

    public function isComplex(): bool
    {
        return true;
    }

    public function convertTypeToJSON(string $outputVarName = 'output'): string
    {
        $key = $this->key;
        $st  = $this->subTypeName();

        return "\${$outputVarName}['$key'] = array_map(function($st \$i) { return \$i->toJson(); }, \$this->$key);";
    }

    /**
     * @param SchemaToClass $generator
     * @throws GeneratorException
     */
    public function generateSubTypes(SchemaToClass $generator): void
    {
        $generator->schemaToClass(
            $this->generatorRequest->withSchema($this->itemSchema)->withClass($this->subTypeName())
        );
    }

    public function typeAnnotation(): string
    {
        return $this->subTypeName() . "[]";
    }

    public function typeHint(string $phpVersion): ?string
    {
        return "array";
    }

    public function generateTypeAssertionExpr(string $expr): string
    {
        $st = $this->subTypeName();
        return "is_array({$expr}) && count(array_filter({$expr}, function({$st} \$item) {return \$item instanceof {$st};})) === count({$expr})";
    }

    public function generateInputAssertionExpr(string $expr): string
    {
        $st = $this->subTypeName();
        return "is_array({$expr}) && count(array_filter({$expr}, function({$st} \$item) {return {$st}::validateInput(\$item, true)};})) === count({$expr})";
    }

    public function generateInputMappingExpr(string $expr, bool $asserted = false): string
    {
        $sm = $this->itemType->generateInputMappingExpr('$i');
        if ($this->generatorRequest->isAtLeastPHP("7.4")) {
            return "array_map(fn (\$i) => {$sm}, {$expr})";
        }
        return "array_map(function(\$i) use (\$validate) { return {$sm}; }, {$expr})";
    }

    public function generateOutputMappingExpr(string $expr): string
    {
        $st = $this->subTypeName();
        $sm = $this->itemType->generateOutputMappingExpr('$i');

        if ($this->generatorRequest->isAtLeastPHP("7.4")) {
            return "array_map(fn ($st \$i) => {$sm}, {$expr})";
        }
        return "array_map(function($st \$i) { return {$sm} }, {$expr})";
    }

    public function generateCloneExpr(string $expr): string
    {
        $st = $this->subTypeName();

        if ($this->generatorRequest->isAtLeastPHP("7.4")) {
            return "array_map(fn ({$st} \$i) => clone \$i, {$expr})";
        }
        return "array_map(function({$st} \$i) { return clone \$i; }, {$expr})";
    }

    private function subTypeName(): string
    {
        return $this->generatorRequest->getTargetClass() . $this->capitalizedName . 'Item';
    }

}
