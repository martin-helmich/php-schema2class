<?php

namespace Helmich\Schema2Class\Generator;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\EnumGenerator\EnumGenerator;
use Laminas\Code\Generator\FileGenerator;

trait GeneratorHookRunner
{
    private array $hooks = [];

    public function withHook(object $hook): self
    {
        $clone          = clone $this;
        $clone->hooks[] = $hook;

        return $clone;
    }

    public function onClassCreated(ClassGenerator $class): void
    {
        foreach ($this->hooks as $hook) {
            if ($hook instanceof Hook\ClassCreatedHook) {
                $hook->onClassCreated($class->getName(), $class);
            }
        }
    }

    public function onEnumCreated(string $enumName, EnumGenerator $enum): void
    {
        foreach ($this->hooks as $hook) {
            if ($hook instanceof Hook\EnumCreatedHook) {
                $hook->onEnumCreated($enumName, $enum);
            }
        }
    }

    public function onFileCreated(string $filename, FileGenerator $fileGenerator): void
    {
        foreach ($this->hooks as $hook) {
            if ($hook instanceof Hook\FileCreatedHook) {
                $hook->onFileCreated($filename, $fileGenerator);
            }
        }
    }
}