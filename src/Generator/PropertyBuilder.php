<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Generator\Property\BooleanProperty;
use Helmich\Schema2Class\Generator\Property\DefaultPropertyDecorator;
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
use Helmich\Schema2Class\Generator\Property\ReferenceArrayProperty;
use Helmich\Schema2Class\Generator\Property\ReferenceProperty;
use Helmich\Schema2Class\Generator\Property\StringEnumProperty;
use Helmich\Schema2Class\Generator\Property\StringProperty;
use Helmich\Schema2Class\Generator\Property\UnionProperty;

class PropertyBuilder
{
    /** @var class-string[] */
    private static array $propertyTypes = [
        IntersectProperty::class,
        UnionProperty::class,
        DateProperty::class,
        StringEnumProperty::class,
        StringProperty::class,
        IntegerProperty::class,
        NumberProperty::class,
        NestedObjectProperty::class,
        ObjectArrayProperty::class,
        ReferenceArrayProperty::class,
        PrimitiveArrayProperty::class,
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
        foreach (self::$propertyTypes as $propertyType) {
            if ($propertyType::canHandleSchema($definition)) {
                /** @var PropertyInterface $property */
                $property = new $propertyType($name, $definition, $req);

                if (isset($definition["default"]) && $req->getOptions()->getTreatValuesWithDefaultAsOptional()) {
                    $property = new DefaultPropertyDecorator($name, $property);
                } else if (!$isRequired) {
                    $property = new OptionalPropertyDecorator($name, $property);
                }

                return $property;
            }
        }

        /** @psalm-suppress PossiblyFalseOperand */
        throw new GeneratorException("cannot map type " . json_encode($definition));
    }
}