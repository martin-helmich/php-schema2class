<?php
namespace Helmich\Schema2Class\Generator\Hook;

use Laminas\Code\Generator\ClassGenerator;

/**
 * Interface definition for hooks that are called when a class is created.
 */
interface ClassCreatedHook
{
    function onClassCreated(string $className, ClassGenerator $class): void;
}