<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

class MixedProperty extends AbstractPropertyInterface
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

    public function typeHint(int $phpVersion)
    {
        return null;
    }

}