<?php

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\ReferenceLookup;

class ReferenceProperty extends AbstractProperty
{
    public function __construct(string $key, array $schema, GeneratorRequest $generatorRequest)
    {
        parent::__construct($key, $schema, $generatorRequest);
    }

    public static function canHandleSchema(array $schema): bool
    {
        return isset($schema['$ref']);
    }

    public function typeAnnotation(): string
    {
        $reference = $this->generatorRequest->lookupReference($this->schema['$ref']);
        if ($reference) {
            return $reference;
        } else {
            return 'mixed';
        }
    }

    public function typeHint(string $phpVersion): ?string
    {
        $reference = $this->generatorRequest->lookupReference($this->schema['$ref']);
        if ($reference) {
            return $reference;
        } else if ($this->generatorRequest->isAtLeastPHP("8.0")) {
            return 'mixed';
        } else {
            return null;
        }
    }

    public function generateTypeAssertionExpr(string $expr): string
    {
        $reference = $this->generatorRequest->lookupReference($this->schema['$ref']);
        if ($reference) {
            return "({$expr}) instanceof {$reference}";
        } else {
            return "is_object({$expr})";
        }
    }

    public function generateInputMappingExpr(string $expr, bool $asserted = false): string
    {
        $reference = $this->generatorRequest->lookupReference($this->schema['$ref']);
        if ($reference) {
            return "{$reference}::buildFromInput({$expr})";
        } else {
            return $expr;
        }
    }

    public function generateOutputMappingExpr(string $expr): string
    {
        $reference = $this->generatorRequest->lookupReference($this->schema['$ref']);
        if ($reference) {
            return "({$expr})->toJson()";
        } else {
            return $expr;
        }
    }

    public function isComplex(): bool
    {
        return true;
    }

}