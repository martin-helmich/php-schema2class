<?php

namespace Helmich\Schema2Class\Generator\Property;

class PropertyCollectionFilter
{
    public static function filterWithoutDeprecatedAndSameName(PropertyCollection $input): PropertyCollection {
        /** @var PropertyInterface[] $properties */
        $properties = iterator_to_array($input);
        $propertyNamesCaseInsensitive = array_unique(array_map(fn (PropertyInterface $p) => strtolower($p->key()), $properties));

        $filtered = [];

        foreach ($properties as $property) {
            if ($property->schema()["deprecated"] && in_array(strtolower($property->key()), $propertyNamesCaseInsensitive)) {
                continue;
            }

            $filtered[] = $property;
        }

        return PropertyCollection::fromArray($filtered);
    }

    public static function isOptional(PropertyInterface $property): bool {
        return $property instanceof OptionalPropertyDecorator;
    }
}