<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Composer\Semver\Semver;

class IntegerProperty extends AbstractProperty
{
    use TypeConvert;

    public static function canHandleSchema(array $schema): bool
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

    public function typeAnnotation(): string
    {
        return "int";
    }

    public function typeHint(string $phpVersion)
    {
        if (Semver::satisfies($phpVersion, "<7.0")) {
            return null;
        }

        return "int";
    }

    public function assertion(string $expr): string
    {
        return "is_int({$expr})";
    }

    public function mapFromInput(string $expr): string
    {
        return "(int)({$expr})";
    }

}