<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

class DateProperty extends AbstractPropertyInterface
{
    use TypeConvert;

    public static function canHandleSchema(array $schema): bool
    {
        return isset($schema["type"])
            && isset($schema["format"])
            && $schema["type"] === "string"
            && $schema["format"] === "date-time";
    }

    public function isComplex(): bool
    {
        return true;
    }

    public function convertJSONToType(string $inputVarName = 'input'): string
    {
        $key = $this->key;
        return "\$$key = new \\DateTime(\${$inputVarName}['$key']);";
    }
    
    public function convertTypeToJSON(string $outputVarName = 'output'): string
    {
        $key = $this->key;
        return "\${$outputVarName}['$key'] = \$this->$key" . "->format(\\DateTime::ATOM);";
    }

    public function cloneProperty(): string
    {
        $key = $this->key;
        return "\$this->$key = clone \$this->$key;";
    }

    public function typeAnnotation(): string
    {
        return "\\DateTime";
    }

    public function typeHint(int $phpVersion): string
    {
        return "\\DateTime";
    }

}
