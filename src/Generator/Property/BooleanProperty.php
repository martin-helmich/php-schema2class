<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Composer\Semver\Semver;

class BooleanProperty extends AbstractProperty
{
    use TypeConvert;

    public static function canHandleSchema(array $schema): bool
    {
        if (!isset($schema["type"])) {
            return false;
        }
        return $schema["type"] === "bool"
            || $schema["type"] === "boolean";
        ;
    }

    public function typeAnnotation(): string
    {
        return "bool";
    }

    public function typeHint(string $phpVersion)
    {
        if (Semver::satisfies($phpVersion, "<7.0")) {
            return null;
        }

        return "bool";
    }

    public function generateTypeAssertionExpr(string $expr): string
    {
        return "is_bool({$expr})";
    }

    public function generateInputMappingExpr(string $expr): string
    {
        return "(bool)({$expr})";
    }

}