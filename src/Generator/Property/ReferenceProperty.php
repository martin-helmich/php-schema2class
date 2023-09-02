<?php

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\ReferenceLookup;

class ReferenceProperty extends AbstractProperty
{
    private ReferenceLookup $referenceLookup;

    public function __construct(string $key, array $schema, GeneratorRequest $generatorRequest)
    {
        parent::__construct($key, $schema, $generatorRequest);
        $this->referenceLookup = $referenceLookup;
    }

    public static function canHandleSchema(array $schema): bool
    {
        return isset($schema['$ref']);
    }

    public function typeAnnotation(): string
    {
        $reference = $this->referenceLookup->lookupReference($this->schema['$ref']);
        if ($reference) {
            return $reference;
        } else {
            return 'mixed';
        }
    }

    public function typeHint(string $phpVersion): ?string
    {
        $reference = $this->referenceLookup->lookupReference($this->schema['$ref']);
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
        $reference = $this->referenceLookup->lookupReference($this->schema['$ref']);
        if ($reference) {
            return "({$expr}) instanceof {$reference}";
        } else {
            return "is_object({$expr})";
        }
    }


}