<?php
declare(strict_types=1);

namespace Helmich\Schema2Class\Generator\Property;

use Composer\Semver\Semver;

class NumberProperty extends AbstractProperty
{
    use TypeConvert;

    public static function canHandleSchema(array $schema): bool
    {
        if (!isset($schema["type"])) {
            return false;
        }
        return $schema["type"] === "number";
    }

    public function typeAnnotation(): string
    {
        return "int|float";
    }

    public function typeHint(string $phpVersion): ?string
    {
        if (Semver::satisfies($phpVersion, "<8.0")) {
            return null;
        }

        return "int|float";
    }

    public function generateTypeAssertionExpr(string $expr): string
    {
        return "is_int({$expr}) || is_float({$expr})";
    }

    public function generateInputMappingExpr(string $expr, bool $asserted = false): string
    {
        if ($asserted) {
            return $expr;
        }

        return "str_contains((string)({$expr}), '.') ? (float)({$expr}) : (int)({$expr})";
    }

}
