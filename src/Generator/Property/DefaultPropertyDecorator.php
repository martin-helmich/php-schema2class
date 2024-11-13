<?php
declare(strict_types=1);

namespace Helmich\Schema2Class\Generator\Property;

use Composer\Semver\Semver;
use Helmich\Schema2Class\Generator\SchemaToClass;

class DefaultPropertyDecorator implements PropertyInterface
{
    use CodeFormatting;

    private string $key;

    private PropertyInterface $inner;

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

    private function defaultExpr(): string
    {
        $default    = $this->schema()["default"];
        $defaultExp = var_export($default, true);

        return $defaultExp === "NULL" ? "null" : $defaultExp;
    }

    /**
     * @param string $inputVarName
     * @param bool $object
     * @return string
     */
    public function convertJSONToType(string $inputVarName = 'input', bool $object = false): string
    {
        $key   = $this->key;
        $name  = $this->name();
        $inner = $this->inner->convertJSONToType($inputVarName, $object);

        $defaultExp = $this->defaultExpr();
        $accessor = $object ? "\${$inputVarName}->{'$key'}" : "\${$inputVarName}['$key']";

        return "\${$name} = {$defaultExp};\nif (isset($accessor)) {\n" . $this->indentCode($inner, 1) . "\n}";
    }

    /**
     * @param string $outputVarName
     * @return string
     */
    public function convertTypeToJSON(string $outputVarName = 'output'): string
    {
        return $this->inner->convertTypeToJSON($outputVarName);
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
        return $this->inner->typeAnnotation();
    }

    public function typeHint(string $phpVersion): ?string
    {
        return $this->inner->typeHint($phpVersion);
    }

    public function cloneProperty(): ?string
    {
        return $this->inner->cloneProperty();
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
        return $this->inner->generateTypeAssertionExpr($expr);
    }

    public function generateInputAssertionExpr(string $expr): string
    {
        return "(({$expr}) === null) || ({$this->inner->generateInputAssertionExpr($expr)})";
    }

    public function generateInputMappingExpr(string $expr, bool $asserted = false): string
    {
        $inner = $this->inner->generateInputMappingExpr($expr);
        return "({$expr} !== null) ? ({$inner}) : {$this->defaultExpr()}";
    }

    public function generateOutputMappingExpr(string $expr): string
    {
        return $this->inner->generateOutputMappingExpr($expr);
    }

    public function generateCloneExpr(string $expr): string
    {
        return $this->inner->generateCloneExpr($expr);
    }

}
