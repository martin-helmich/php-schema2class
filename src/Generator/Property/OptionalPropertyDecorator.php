<?php
declare(strict_types=1);

namespace Helmich\Schema2Class\Generator\Property;

use Composer\Semver\Semver;
use Helmich\Schema2Class\Generator\SchemaToClass;
use Laminas\Code\Generator\PropertyValueGenerator;

class OptionalPropertyDecorator implements PropertyInterface
{
    use CodeFormatting;

    private string $key;

    private PropertyInterface $inner;

    /**
     * OptionalPropertyDecorator constructor.
     * @param                   $key
     * @param PropertyInterface $inner
     */
    public function __construct(string $key, PropertyInterface $inner)
    {
        $this->key   = $key;
        $this->inner = $inner;
    }

    /**
     * @return bool
     */
    public function isComplex(): bool
    {
        return $this->inner->isComplex();
    }

    /**
     * @param array $schema
     * @return bool
     */
    public static function canHandleSchema(array $schema): bool
    {
        return false;
    }

    /**
     * @param string $inputVarName
     * @param bool $object
     * @return string
     */
    public function convertJSONToType(string $inputVarName = 'input', bool $object = false): string
    {
        $key   = $this->key;
        $name  = $this->inner->name();
        $inner = $this->inner->convertJSONToType($inputVarName, $object);

        $default    = isset($this->schema()["default"]) ? $this->schema()["default"] : null;
        $defaultExp = rtrim($this->formatValue($default)?->generate() ?? "null", ";");

        $accessor = $object ? "\${$inputVarName}->{'$key'}" : "\${$inputVarName}['$key']";

        return "\${$name} = {$defaultExp};\nif (isset($accessor)) {\n" . $this->indentCode($inner, 1) . "\n}";
    }

    /**
     * @param string $outputVarName
     * @return string
     */
    public function convertTypeToJSON(string $outputVarName = 'output'): string
    {
        $name  = $this->inner->name();
        $inner = $this->inner->convertTypeToJSON($outputVarName);

        return "if (isset(\$this->{$name})) {\n" . $this->indentCode($inner, 1) . "\n}";
    }

    /**
     * @param SchemaToClass $generator
     * @return void
     */
    public function generateSubTypes(SchemaToClass $generator): void
    {
        $this->inner->generateSubTypes($generator);
    }

    /**
     * @return string
     */
    public function typeAnnotation(): string
    {
        $inner = $this->inner->typeAnnotation();
        if (!str_contains($inner, "|null")) {
            $inner .= "|null";
        }

        return $inner;
    }

    /**
     * @param $phpVersion
     * @return string|null
     */
    public function typeHint(string $phpVersion): ?string
    {
        $inner = $this->inner->typeHint($phpVersion);

        if (Semver::satisfies($phpVersion, "<7.0")) {
            return $inner;
        }

        if ($inner === null) {
            return null;
        }

        if (Semver::satisfies($phpVersion, ">=8.0") && str_contains($inner, "|")) {
            return "{$inner}|null";
        }

        if (Semver::satisfies($phpVersion, ">=7.1.0") && strpos($inner, "?") !== 0) {
            if ($inner === "mixed" || $inner === "null") {
                return $inner;
            }

            $inner = "?" . $inner;
        }

        return $inner;
    }

    /**
     * @return string|null
     */
    public function cloneProperty(): ?string
    {
        $name  = $this->name();
        $inner = $this->inner->cloneProperty();

        if ($inner !== null) {
            return "if (isset(\$this->{$name})) {\n" . $this->indentCode($inner, 1) . "\n}";
        }

        return null;
    }

    /**
     * @return array
     */
    public function schema(): array
    {
        return $this->inner->schema();
    }

    /**
     * @return string
     */
    public function key(): string
    {
        return $this->inner->key();
    }

    public function name(): string
    {
        return $this->inner->name();
    }

    /**
     * @return PropertyInterface
     */
    public function unwrap(): PropertyInterface
    {
        return $this->inner;
    }

    public function generateTypeAssertionExpr(string $expr): string
    {
        return "(({$expr}) === null) || ({$this->inner->generateTypeAssertionExpr($expr)})";
    }

    public function generateInputAssertionExpr(string $expr): string
    {
        return "(({$expr}) === null) || ({$this->inner->generateInputAssertionExpr($expr)})";
    }

    public function generateInputMappingExpr(string $expr, bool $asserted = false): string
    {
        $inner = $this->inner->generateInputMappingExpr($expr);
        return "({$expr} !== null) ? ({$inner}) : null";
    }

    public function generateOutputMappingExpr(string $expr): string
    {
        $inner = $this->inner->generateOutputMappingExpr($expr);
        return "({$expr} !== null) ? ({$inner}) : null";
    }

    public function generateCloneExpr(string $expr): string
    {
        return "isset({$expr}) ? (clone {$expr}) : null";
    }

    public function formatValue(mixed $value): PropertyValueGenerator|null
    {
        return $this->inner->formatValue($value);
    }

}
