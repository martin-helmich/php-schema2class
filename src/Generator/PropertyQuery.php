<?php
namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Generator\Property\PropertyInterface;

class PropertyQuery
{
    public static function isDeprecated(PropertyInterface $property): bool
    {
        $schema = $property->schema();
        return isset($schema["deprecated"]) && $schema["deprecated"];
    }
}