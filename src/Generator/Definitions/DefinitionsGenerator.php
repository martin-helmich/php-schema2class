<?php

declare(strict_types=1);

namespace Helmich\Schema2Class\Generator\Definitions;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;

class DefinitionsGenerator
{
    public function __construct(
        private SchemaToClass $schemaToClass,
    )
    {
    }

    /**
     * @param array<string, Definition> $definitions
     */
    public function generate(array $definitions, GeneratorRequest $generatorRequest): void
    {
        foreach ($definitions as $definition) {
            $newRequest = $generatorRequest->withClass($definition->className)
                ->withSchema($definition->schema)
                ->withNamespace(join('\\', [$generatorRequest->getTargetNamespace(), $definition->namespace]))
                ->withDirectory(join('/', [$generatorRequest->getTargetDirectory(), $definition->directory]))
            ;

            $this->schemaToClass->schemaToClass($newRequest);
        }
    }
}