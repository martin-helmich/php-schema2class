<?php

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\ReferencedType;

class ReferenceArrayProperty extends AbstractProperty
{
    private ReferencedType $type;

    public function __construct(string $key, array $schema, GeneratorRequest $generatorRequest)
    {
        parent::__construct($key, $schema, $generatorRequest);
        $this->type = $generatorRequest->lookupReference($schema['items']['$ref']);
    }

    public static function canHandleSchema(array $schema): bool
    {
        return isset($schema['type']) && $schema['type'] === 'array' && isset($schema['items']['$ref']);
    }

    public function typeAnnotation(): string
    {
        return $this->type->typeAnnotation($this->generatorRequest) . '[]';
    }

    public function typeHint(string $phpVersion): ?string
    {
        return "array";
    }

    public function generateTypeAssertionExpr(string $expr): string
    {
        $map = "array_map(fn({$this->type->typeHint($this->generatorRequest)} \$i): bool => {$this->type->typeAssertionExpr($this->generatorRequest, '$i')}, {$expr})";
        return "array_reduce($map, fn(bool \$carry, bool \$item): bool => \$carry && \$item, true)";
    }

    public function generateInputAssertionExpr(string $expr): string
    {
        $map = "array_map(fn({$this->type->serializedTypeHint($this->generatorRequest)} \$i): bool => {$this->type->inputAssertionExpr($this->generatorRequest, '$i')}, {$expr})";
        return "array_reduce($map, fn(bool \$carry, bool \$item): bool => \$carry && \$item, true)";
    }

    public function generateInputMappingExpr(string $expr, bool $asserted = false): string
    {
        return "array_map(fn({$this->type->serializedTypeHint($this->generatorRequest)} \$i): {$this->type->typeHint($this->generatorRequest)} => {$this->type->inputMappingExpr($this->generatorRequest, '$i')}, {$expr})";
    }

    public function generateOutputMappingExpr(string $expr): string
    {
        return "array_map(fn({$this->type->typeHint($this->generatorRequest)} \$i): {$this->type->serializedTypeHint($this->generatorRequest)} => {$this->type->outputMappingExpr($this->generatorRequest, '$i')}, {$expr})";
    }

    public function isComplex(): bool
    {
        return true;
    }

}