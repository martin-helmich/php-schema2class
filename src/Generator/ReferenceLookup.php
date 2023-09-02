<?php
namespace Helmich\Schema2Class\Generator;

interface ReferenceLookup
{
    public function lookupReference(string $reference): ReferenceLookupResult;
}