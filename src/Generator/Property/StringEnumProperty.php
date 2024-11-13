<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Composer\Semver\Semver;
use Helmich\Schema2Class\Generator\GeneratorException;
use Helmich\Schema2Class\Generator\SchemaToClass;
use Helmich\Schema2Class\Generator\SchemaToEnum;
use Laminas\Code\Generator\PropertyValueGenerator;
use Laminas\Code\Generator\ValueGenerator;

class StringEnumProperty extends AbstractProperty
{
    use TypeConvert;

    public static function canHandleSchema(array $schema): bool
    {
        return isset($schema["type"]) && $schema["type"] === "string" && isset($schema["enum"]);
    }

    public function isComplex(): bool
    {
        return true;
    }

    /**
     * @param SchemaToClass    $generator
     * @throws GeneratorException
     */
    public function generateSubTypes(SchemaToClass $generator): void
    {
        $generator->schemaToClass(
            $this->generatorRequest
                ->withSchema($this->schema)
                ->withClass($this->subTypeName())
        );
    }

    public function typeAnnotation(): string
    {
        return $this->subTypeName();
    }

    public function typeHint(string $phpVersion): ?string
    {
        return "\\" . $this->generatorRequest->getTargetNamespace() . "\\" . $this->subTypeName();
    }

    public function generateTypeAssertionExpr(string $expr): string
    {
        return "{$expr} instanceof {$this->subTypeName()}";
    }

    public function generateInputAssertionExpr(string $expr): string
    {
        return "{$this->subTypeName()}::tryFrom({$expr}) !== null";
    }

    public function generateInputMappingExpr(string $expr, bool $asserted = false): string
    {
        return "{$this->subTypeName()}::from({$expr})";
    }

    public function generateOutputMappingExpr(string $expr): string
    {
        return "({$expr})->value";
    }

    public function generateCloneExpr(string $expr): string
    {
        return $expr;
    }

    private function subTypeName(): string
    {
        return $this->generatorRequest->getTargetClass() . $this->capitalizedName;
    }

    public function formatValue(mixed $value): PropertyValueGenerator
    {
        if ($value === null) {
            return new PropertyValueGenerator(null);
        }

        // Using TYPE_CONSTANT is a dirty workaround to bypass PropertyValueGenerator's formatting.
        // Ideally, we would want to use TYPE_ENUM, but this requires the referenced enum to exist
        // as a class WHILE generating.
        return new PropertyValueGenerator(
            $this->subTypeName() . "::" . SchemaToEnum::enumCaseName($value),
            ValueGenerator::TYPE_CONSTANT,
        );
    }

}
