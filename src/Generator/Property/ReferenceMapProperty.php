<?php

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\ReferencedType;

/**
 * Handles map schemas of the form {type: object, additionalProperties: {$ref: ...}}.
 * Values are typed as the referenced class; the map key is always a string.
 */
class ReferenceMapProperty extends AbstractProperty
{
    private ReferencedType $type;

    public function __construct(string $key, array $schema, GeneratorRequest $generatorRequest)
    {
        parent::__construct($key, $schema, $generatorRequest);
        $this->type = $generatorRequest->lookupReference($schema['additionalProperties']['$ref']);
    }

    public static function canHandleSchema(array $schema): bool
    {
        return isset($schema['additionalProperties']['$ref'])
            && (!isset($schema['type']) || $schema['type'] === 'object');
    }

    public function isComplex(): bool
    {
        return true;
    }

    public function typeAnnotation(): string
    {
        $inner = $this->type->typeAnnotation($this->generatorRequest);
        return "array<string, {$inner}>";
    }

    public function typeHint(string $phpVersion): ?string
    {
        return "array";
    }

    public function generateTypeAssertionExpr(string $expr): string
    {
        $assertItem = $this->type->typeAssertionExpr($this->generatorRequest, '$v');
        return "is_array({$expr}) && array_reduce(array_map(fn(\$v) => {$assertItem}, {$expr}), fn(bool \$c, bool \$i) => \$c && \$i, true)";
    }

    public function generateInputAssertionExpr(string $expr): string
    {
        $assertItem = $this->type->inputAssertionExpr($this->generatorRequest, '$v');
        return "is_array({$expr}) && array_reduce(array_map(fn(\$v) => {$assertItem}, {$expr}), fn(bool \$c, bool \$i) => \$c && \$i, true)";
    }

    public function generateInputMappingExpr(string $expr, bool $asserted = false): string
    {
        $mapItem = $this->type->inputMappingExpr($this->generatorRequest, expr: '$v', validateExpr: null);
        $typeHint = $this->type->serializedInputTypeHint($this->generatorRequest);
        return "array_map(fn({$typeHint} \$v) => {$mapItem}, (array){$expr})";
    }

    public function generateOutputMappingExpr(string $expr): string
    {
        $mapItem  = $this->type->outputMappingExpr($this->generatorRequest, '$v');
        $typeHint = $this->type->typeHint($this->generatorRequest);
        return "array_map(fn({$typeHint} \$v) => {$mapItem}, {$expr})";
    }


}
