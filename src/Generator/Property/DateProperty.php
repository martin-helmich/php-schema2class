<?php
namespace Helmich\Schema2Class\Generator\Property;

class DateProperty extends AbstractPropertyInterface
{
    use TypeConvert;

    public static function canHandleSchema(array $schema)
    {
        return isset($schema["type"])
            && isset($schema["format"])
            && $schema["type"] === "string"
            && $schema["format"] === "date-time";
    }

    public function isComplex()
    {
        return true;
    }

    public function convertJSONToType($inputVarName = 'input')
    {
        $key = $this->key;
        return "\$$key = new \\DateTime(\${$inputVarName}['$key']);";
    }
    
    public function convertTypeToJSON($outputVarName = 'output') {
        $key = $this->key;
        return "\${$outputVarName}['$key'] = \$this->$key" . "->format(\\DateTime::ATOM);";
    }

    public function cloneProperty()
    {
        $key = $this->key;
        return "\$this->$key = clone \$this->$key;";
    }

    public function typeAnnotation()
    {
        return "\\DateTime";
    }

    public function typeHint($phpVersion)
    {
        return "\\DateTime";
    }

}
