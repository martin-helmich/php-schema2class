<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Generator\Property\BooleanProperty;
use Helmich\Schema2Class\Generator\Property\NumberProperty;
use Helmich\Schema2Class\Generator\Property\ObjectArrayProperty;
use Helmich\Schema2Class\Generator\Property\PrimitiveArrayProperty;
use Helmich\Schema2Class\Generator\Property\DateProperty;
use Helmich\Schema2Class\Generator\Property\IntegerProperty;
use Helmich\Schema2Class\Generator\Property\IntersectProperty;
use Helmich\Schema2Class\Generator\Property\MixedProperty;
use Helmich\Schema2Class\Generator\Property\NestedObjectProperty;
use Helmich\Schema2Class\Generator\Property\OptionalPropertyDecorator;
use Helmich\Schema2Class\Generator\Property\PropertyInterface;
use Helmich\Schema2Class\Generator\Property\ReferenceProperty;
use Helmich\Schema2Class\Generator\Property\StringEnumProperty;
use Helmich\Schema2Class\Generator\Property\StringProperty;
use Helmich\Schema2Class\Generator\Property\UnionProperty;

class PropertyBuilder
{
    /** @var string[] */
    private static array $propertyTypes = [
        IntersectProperty::class,
        UnionProperty::class,
        DateProperty::class,
        StringEnumProperty::class,
        StringProperty::class,
        PrimitiveArrayProperty::class,
        ObjectArrayProperty::class,
        IntegerProperty::class,
        NumberProperty::class,
        NestedObjectProperty::class,
        BooleanProperty::class,
        ReferenceProperty::class,
        MixedProperty::class,
    ];

    /**
     * @param GeneratorRequest $req
     * @param string           $name
     * @param array            $definition
     * @param bool             $isRequired
     * @return PropertyInterface
     * @throws GeneratorException
     */
    public static function buildPropertyFromSchema(GeneratorRequest $req, string $name, array $definition, bool $isRequired): PropertyInterface
    {
        static::testInvariants($definition);

        foreach (static::$propertyTypes as $propertyType) {
            if ($propertyType::canHandleSchema($definition)) {
                /** @var PropertyInterface $property */
                $property = new $propertyType($name, $definition, $req);

                if (!$isRequired) {
                    $property = new OptionalPropertyDecorator($name, $property);
                }

                return $property;
            }
        }

        throw new GeneratorException("cannot map type " . json_encode($definition));
    }

    private static function testInvariants(array $definition): void
    {
        if (isset($definition["properties"]) && isset($definition["additionalProperties"])) {
            throw new GeneratorException("using 'properties' and 'additionalProperties in the same schema is currently not supported.");
        }
    }
}