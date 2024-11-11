<?php

declare(strict_types=1);

namespace Helmich\Schema2Class\Generator\Definitions;

use Helmich\Schema2Class\Generator\GeneratorRequest;

class DefinitionsCollector
{
    public function __construct(protected readonly GeneratorRequest $generatorRequest)
    {
    }

    /**
     * @return \Generator<string, Definition>
     */
    public function collect(array $schema, string $path = ''): \Generator {
        if (isset($schema['definitions'])) {
            yield from $this->findNestedDefinitions($schema['definitions'], ($path ?: '#') . '/definitions');
        }

        if (isset($schema['$defs'])) {
            yield from $this->findNestedDefinitions($schema['$defs'], ($path ?: '#') . '/$defs');
        }
    }

    private function findNestedDefinitions(array $definitions, string $path): \Generator {
        foreach ($definitions as $key => $value) {
            $newPath = $path . '/' . $key;
            yield $newPath => $this->pathToClassName($newPath, $value);

            if (is_array($value)) {
                yield from $this->collect($value, $newPath);
            }
        }
    }

    private function pathToClassName(string $path, array $schema): Definition {
        $path = str_replace('$defs', 'Defs', $path);
        // Strips out the `#` symbol and splits the path into parts
        $parts = explode('/', ltrim($path, '#/'));

        // Maps each part of the path to a StudlyCase (or PascalCase) conversion
        $classNameParts = array_map(function ($part) {
            return str_replace(' ', '', ucwords(str_replace('_', ' ', $part)));
        }, $parts);

        $classFQN = $this->generatorRequest->getTargetNamespace() . '\\' . implode('\\', $classNameParts);
        $className = array_pop($classNameParts);

        // Joins the parts back into a string with slashes (to represent namespace hierarchy)
        return new Definition(
            namespace: $this->generatorRequest->getTargetNamespace() . '\\' . implode('\\', $classNameParts),
            directory: $this->generatorRequest->getTargetDirectory() . '/' . implode('/', $classNameParts),
            classFQN: $classFQN,
            className: $className,
            schema: $schema,
        );
    }
}