<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Composer\Semver\Comparator;
use Composer\Semver\Semver;

class IntegerProperty extends AbstractPropertyInterface
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

    public function convertJSONToType(string $inputVarName = 'input'): string
    {
        $key = $this->key;
        return "\$$key = (int) \${$inputVarName}['$key'];";
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

}