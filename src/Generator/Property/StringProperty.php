<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Composer\Semver\Semver;

class StringProperty extends AbstractProperty
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
     * @param string $phpVersion
     * @return string|null
     */
    public function typeHint(string $phpVersion): ?string
    {
        if (Semver::satisfies($phpVersion, "<7.0")) {
            return null;
        }

        return "string";
    }

    public function generateTypeAssertionExpr(string $expr): string
    {
        return "is_string({$expr})";
    }

}
