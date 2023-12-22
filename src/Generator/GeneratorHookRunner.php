<?php

namespace Helmich\Schema2Class\Generator;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\EnumGenerator\EnumGenerator;
use Laminas\Code\Generator\FileGenerator;

trait GeneratorHookRunner
{
    /**
     * @var array<array{hook: object, propagateToSubObjects: bool}>
     */
    private array $hooks = [];

    public function withHook(object $hook, bool $propagateToSubObjects = true): self
    {
        $clone          = clone $this;
        $clone->hooks[] = [
            "hook"                  => $hook,
            "propagateToSubObjects" => $propagateToSubObjects,
        ];

        return $clone;
    }

    private function clearNonPropagatingHooks(): void
    {
        $this->hooks = array_filter($this->hooks, fn($hook) => $hook["propagateToSubObjects"]);
    }

    public function onClassCreated(ClassGenerator $class): void
    {
        foreach ($this->hooks as ['hook' => $hook]) {
            if ($hook instanceof Hook\ClassCreatedHook) {
                $hook->onClassCreated($class->getName(), $class);
            }
        }
    }

    public function onEnumCreated(string $enumName, EnumGenerator $enum): void
    {
        foreach ($this->hooks as ['hook' => $hook]) {
            if ($hook instanceof Hook\EnumCreatedHook) {
                $hook->onEnumCreated($enumName, $enum);
            }
        }
    }

    public function onFileCreated(string $filename, FileGenerator $fileGenerator): void
    {
        foreach ($this->hooks as ['hook' => $hook]) {
            if ($hook instanceof Hook\FileCreatedHook) {
                $hook->onFileCreated($filename, $fileGenerator);
            }
        }
    }
}