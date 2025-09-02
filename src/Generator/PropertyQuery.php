<?php
namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Generator\Property\PropertyInterface;

class PropertyQuery
{
    public static function isDeprecated(PropertyInterface $property): bool
    {
        $schema = $property->schema();
        if (isset($schema["deprecated"]) && $schema["deprecated"]) {
            return true;
        }

        if (isset($schema["allOf"])) {
            foreach ($schema["allOf"] as $subSchema) {
                if (isset($subSchema["deprecated"]) && $subSchema["deprecated"]) {
                    return true;
                }
            }
        }

        return false;
    }
}