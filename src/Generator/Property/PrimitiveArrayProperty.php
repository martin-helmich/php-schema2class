<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorException;
use Helmich\Schema2Class\Generator\SchemaToClass;

class PrimitiveArrayProperty extends AbstractPropertyInterface
{
    use TypeConvert;

    public static function canHandleSchema(array $schema): bool
    {
        $isArray = isset($schema["type"]) && $schema["type"] === "array";
        if (!$isArray) {
            return false;
        }

        return !ObjectArrayProperty::canHandleSchema($schema);
    }


    public function isComplex(): bool
    {
        return false;
    }

    public function cloneProperty(): string
    {
        $key = $this->key;

        return "\$this->$key = clone \$this->$key;";
    }

    /**
     * @param SchemaToClass    $generator
     */
    public function generateSubTypes(SchemaToClass $generator): void
    {
    }

    public function typeAnnotation(): string
    {
        if (isset($this->schema["items"])) {
            [$annot, $hint] = $this->phpPrimitiveForSchemaType($this->schema["items"]);
            return $annot . "[]";
        }

        return "array";
    }

    public function typeHint(string $phpVersion): string
    {
        return "array";
    }

    public function assertion(string $expr): string
    {
        return "is_array({$expr})";
    }

}