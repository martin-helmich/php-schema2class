<?php
declare(strict_types=1);

namespace Helmich\Schema2Class\Generator;

readonly class ReferenceLookupResult
{
    public function __construct(public string $name, public ReferenceLookupResultType $type)
    {
    }
}