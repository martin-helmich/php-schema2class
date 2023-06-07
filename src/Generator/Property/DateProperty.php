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

    public function typeAnnotation(): string
    {
        return "\\DateTime";
    }

    public function typeHint(string $phpVersion): ?string
    {
        return "\\DateTime";
    }

    public function generateTypeAssertionExpr(string $expr): string
    {
        return "{$expr} instanceof \\DateTime";
    }

    public function generateInputMappingExpr(string $expr, bool $asserted = false): string
    {
        if ($asserted) {
            return $expr;
        }

        return "new \\DateTime({$expr})";
    }

    public function generateOutputMappingExpr(string $expr): string
    {
        return "({$expr})->format(\\DateTime::ATOM)";
    }

    public function generateCloneExpr(string $expr): string
    {
        return "clone {$expr}";
    }

}
