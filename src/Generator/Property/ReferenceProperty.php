<?php

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\ReferenceLookupResultType;

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
        return match ($reference->type) {
            ReferenceLookupResultType::TYPE_CLASS, ReferenceLookupResultType::TYPE_ENUM => "\\" . $reference->name,
            ReferenceLookupResultType::TYPE_UNKNOWN => 'mixed',
        };
    }

    public function typeHint(string $phpVersion): ?string
    {
        $reference = $this->generatorRequest->lookupReference($this->schema['$ref']);
        return match ($reference->type) {
            ReferenceLookupResultType::TYPE_CLASS, ReferenceLookupResultType::TYPE_ENUM => "\\" . $reference->name,
            ReferenceLookupResultType::TYPE_UNKNOWN => $this->generatorRequest->isAtLeastPHP("8.0") ? 'mixed' : null
        };
    }

    public function generateTypeAssertionExpr(string $expr): string
    {
        $reference = $this->generatorRequest->lookupReference($this->schema['$ref']);
        return match ($reference->type) {
            ReferenceLookupResultType::TYPE_CLASS, ReferenceLookupResultType::TYPE_ENUM => "({$expr}) instanceof \\{$reference->name}",
            ReferenceLookupResultType::TYPE_UNKNOWN => "is_object({$expr})",
        };
    }

    public function generateInputMappingExpr(string $expr, bool $asserted = false): string
    {
        $reference = $this->generatorRequest->lookupReference($this->schema['$ref']);
        return match ($reference->type) {
            ReferenceLookupResultType::TYPE_CLASS => "\\{$reference->name}::buildFromInput({$expr})",
            ReferenceLookupResultType::TYPE_ENUM => "\\{$reference->name}::from({$expr})",
            ReferenceLookupResultType::TYPE_UNKNOWN => $expr,
        };
    }

    public function generateOutputMappingExpr(string $expr): string
    {
        $reference = $this->generatorRequest->lookupReference($this->schema['$ref']);
        return match ($reference->type) {
            ReferenceLookupResultType::TYPE_CLASS => "{$expr}->toJson()",
            ReferenceLookupResultType::TYPE_ENUM => "{$expr}->value",
            ReferenceLookupResultType::TYPE_UNKNOWN => $expr,
        };
    }

    public function isComplex(): bool
    {
        return true;
    }

}