<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

class DateProperty extends AbstractProperty
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

    public function cloneProperty(): string
    {
        $key = $this->key;
        return "\$this->$key = clone \$this->$key;";
    }

    public function typeAnnotation(): string
    {
        return "\\DateTime";
    }

    public function typeHint(string $phpVersion): string
    {
        return "\\DateTime";
    }

    public function assertion(string $expr): string
    {
        return "${expr} instanceof \\DateTime";
    }

    public function mapFromInput(string $expr): string
    {
        return "new \\DateTime({$expr})";
    }

    public function mapToOutput(string $expr): string
    {
        return "({$expr})->format(\\DateTime::ATOM)";
    }

}
