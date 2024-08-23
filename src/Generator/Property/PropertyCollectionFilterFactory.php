<?php

namespace Helmich\Schema2Class\Generator\Property;

readonly class PropertyCollectionFilterFactory
{
    public static function withoutDeprecatedAndSameName(PropertyCollection $properties): PropertyCollectionFilter
    {
        return new class($properties) implements PropertyCollectionFilter {
            private array $propertyNamesCaseInsensitive;

            public function __construct(PropertyCollection $properties)
            {
                /** @var PropertyInterface[] $properties */
                $properties                         = iterator_to_array($properties);
                $this->propertyNamesCaseInsensitive = array_unique(array_map(fn(PropertyInterface $p) => strtolower($p->key()), $properties));
            }

            public function apply(PropertyInterface $property): bool
            {
                if ($property->schema()["deprecated"] && in_array(strtolower($property->key()), $this->propertyNamesCaseInsensitive)) {
                    return false;
                }

                return true;
            }
        };
    }

    public static function optional(): PropertyCollectionFilter
    {
        return new class implements PropertyCollectionFilter {
            public function apply(PropertyInterface $property): bool
            {
                return $property instanceof OptionalPropertyDecorator;
            }
        };
    }

    public static function required(): PropertyCollectionFilter
    {
        return new class implements PropertyCollectionFilter {
            public function apply(PropertyInterface $property): bool
            {
                return !($property instanceof OptionalPropertyDecorator);
            }
        };
    }
}