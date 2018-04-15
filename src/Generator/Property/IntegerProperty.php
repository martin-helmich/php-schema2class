<?php
namespace Helmich\JsonStructBuilder\Generator\Property;

class IntegerProperty extends AbstractPropertyInterface
{
    use TypeConvert;

    public static function canHandleSchema(array $schema)
    {
        if (!isset($schema["type"])) {
            return false;
        }
        return $schema["type"] === "integer"
            || $schema["type"] === "int"
            || (isset($schema["format"]) && $schema["type"] === "number" && $schema["format"] === "integer")
            || (isset($schema["format"]) && $schema["type"] === "number" && $schema["format"] === "int")
        ;
    }

    public function convertJSONToType($inputVarName = 'input')
    {
        $key = $this->key;
        return "\$$key = (int) \${$inputVarName}['$key'];";
    }

    public function typeAnnotation()
    {
        return "int";
    }

    public function typeHint($phpVersion)
    {
        if ($phpVersion === 5) {
            return null;
        }

        return "int";
    }

}