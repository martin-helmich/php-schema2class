<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

class StringProperty extends AbstractPropertyInterface
{
    use TypeConvert;

    public static function canHandleSchema(array $schema): bool
    {
        return isset($schema["type"]) && $schema["type"] === "string";
    }

    public function typeAnnotation(): string
    {
        return "string";
    }

    /**
     * @return string|null
     */
    public function typeHint(int $phpVersion)
    {
        if ($phpVersion === 5) {
            return null;
        }

        return "string";
    }

}