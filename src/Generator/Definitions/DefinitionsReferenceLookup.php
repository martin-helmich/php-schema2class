<?php

declare(strict_types=1);

namespace Helmich\Schema2Class\Generator\Definitions;

use Helmich\Schema2Class\Generator\ReferencedType;
use Helmich\Schema2Class\Generator\ReferencedTypeClass;
use Helmich\Schema2Class\Generator\ReferencedTypeUnknown;
use Helmich\Schema2Class\Generator\ReferenceLookup;

readonly class DefinitionsReferenceLookup implements ReferenceLookup
{
    /**
     * @param array<string, Definition> $definitions
     */
    public function __construct(
        private array $definitions
    )
    {
    }

    public function lookupReference(string $reference): ReferencedType
    {
        if (isset($this->definitions[$reference])) {
            return new ReferencedTypeClass($this->definitions[$reference]->classFQN);
        }
        return new ReferencedTypeUnknown();
    }

    public function lookupSchema(string $reference): array
    {
        if (isset($this->definitions[$reference])) {
            return $this->definitions[$reference]->schema;
        }
        return [];
    }
}