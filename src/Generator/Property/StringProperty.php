<?php
namespace Helmich\JsonStructBuilder\Generator\Property;

class StringProperty extends AbstractPropertyInterface
{
    use TypeConvert;

    public static function canHandleSchema(array $schema)
    {
        return isset($schema["type"]) && $schema["type"] === "string";
    }

    public function typeAnnotation()
    {
        return "string";
    }

    public function typeHint($phpVersion)
    {
        if ($phpVersion === 5) {
            return null;
        }

        return "string";
    }

}