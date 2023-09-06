<?php

namespace Helmich\Schema2Class\Generator\Hook;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;

readonly class AddMethodHook implements ClassCreatedHook
{
    public function __construct(private MethodGenerator $method)
    {
    }

    function onClassCreated(string $className, ClassGenerator $class): void
    {
        $class->addMethodFromGenerator($this->method);
    }
}