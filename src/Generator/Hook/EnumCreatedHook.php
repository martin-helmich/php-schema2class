<?php

namespace Helmich\Schema2Class\Generator\Hook;

use Laminas\Code\Generator\EnumGenerator\EnumGenerator;

/**
 * Interface definition for hooks that are called when an enumeration is created.
 */
interface EnumCreatedHook
{
    function onEnumCreated(string $enumName, EnumGenerator $enum): void;
}