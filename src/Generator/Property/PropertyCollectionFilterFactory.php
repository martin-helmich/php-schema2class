<?php

namespace Helmich\Schema2Class\Generator\Property;

readonly class PropertyCollectionFilterFactory
{
    public static function withoutDeprecatedAndSameName(PropertyCollection $properties): PropertyCollectionFilter
    {
        return new class($properties) implements PropertyCollectionFilter {
            private array $propertyNamesCaseInsensitive = [];

            public function __construct(PropertyCollection $properties)
            {
                foreach ($properties as $property) {
                    $caseInsensitiveName = strtolower($property->key());
                    if (!isset($this->propertyNamesCaseInsensitive[$caseInsensitiveName])) {
                        $this->propertyNamesCaseInsensitive[$caseInsensitiveName] = [];
                    }

                    $this->propertyNamesCaseInsensitive[$caseInsensitiveName][] = $property->key();
                }
            }

            public function apply(PropertyInterface $property): bool
            {
                $schema = $property->schema();

                $isDeprecated = isset($schema["deprecated"]) && $schema["deprecated"];
                $matchingProperties = $this->propertyNamesCaseInsensitive[strtolower($property->key())];
                $matchingPropertiesWithDifferentCase = array_filter($matchingProperties, fn($name) => $name !== $property->key());

                if ($isDeprecated && count($matchingPropertiesWithDifferentCase) > 0) {
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