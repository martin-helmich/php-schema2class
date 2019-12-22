<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

class MixedProperty extends AbstractProperty
{
    use TypeConvert;

    public static function canHandleSchema(array $schema): bool
    {
        return true;
    }

    public function typeAnnotation(): string
    {
        return "mixed";
    }

    public function typeHint(string $phpVersion)
    {
        return null;
    }

    public function assertion(string $expr): string
    {
        return "true";
    }

}