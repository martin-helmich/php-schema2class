<?php

namespace Helmich\Schema2Class\Generator\Hook;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;

readonly class AddPropertyHook implements ClassCreatedHook
{
    public function __construct(private PropertyGenerator $property)
    {
    }

    function onClassCreated(string $className, ClassGenerator $class): void
    {
        $class->addPropertyFromGenerator($this->property);
    }
}