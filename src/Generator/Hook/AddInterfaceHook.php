<?php

namespace Helmich\Schema2Class\Generator\Hook;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;

readonly class AddInterfaceHook implements ClassCreatedHook
{
    /**
     * @psalm-param class-string $interface
     * @param string $interface
     */
    public function __construct(private string $interface)
    {
    }

    function onClassCreated(string $className, ClassGenerator $class): void
    {
        $interfaces = [...$class->getImplementedInterfaces(), $this->interface];
        $class->setImplementedInterfaces($interfaces);
    }
}