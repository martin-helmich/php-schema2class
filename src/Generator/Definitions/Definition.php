<?php

declare(strict_types=1);

namespace Helmich\Schema2Class\Generator\Definitions;

readonly class Definition
{
    public function __construct(
        public string $namespace,
        public string $directory,
        public string $classFQN,
        public string $className,
        public array $schema,
    )
    {
    }
}