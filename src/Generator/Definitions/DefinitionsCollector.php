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
        $parts = array_map(
            fn ($part) => str_replace(' ', '', ucwords(str_replace('_', ' ', $part))),
            explode('/', ltrim(str_replace('$defs', 'Defs', $path), '#/'))
        );

        $className = array_pop($parts);
        $namespace = $this->generatorRequest->getTargetNamespace() . '\\' . implode('\\', $parts);
        $directory = $this->generatorRequest->getTargetDirectory() . '/' . implode('/', $parts);

        return new Definition(
            namespace: $namespace,
            directory: $directory,
            classFQN: $namespace . '\\' . $className,
            className: $className,
            schema: $schema,
        );
    }
}