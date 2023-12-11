<?php
namespace Helmich\Schema2Class\Generator;

interface ReferenceLookup
{
    public function lookupReference(string $reference): ReferencedType;
    public function lookupSchema(string $reference): array;
}