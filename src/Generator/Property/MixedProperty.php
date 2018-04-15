<?php
namespace Helmich\Schema2Class\Generator\Property;

class MixedProperty extends AbstractPropertyInterface
{
    use TypeConvert;

    public static function canHandleSchema(array $schema)
    {
        return true;
    }

    public function typeAnnotation()
    {
        return "mixed";
    }

    public function typeHint($phpVersion)
    {
        return null;
    }

}