<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\SchemaToClass;

class OptionalPropertyDecorator implements PropertyInterface
{
    use CodeFormatting;

    /** @var string */
    private $key;

    /** @var PropertyInterface */
    private $inner;

    /**
     * OptionalPropertyDecorator constructor.
     * @param                   $key
     * @param PropertyInterface $inner
     */
    public function __construct(string $key, PropertyInterface $inner)
    {
        $this->key = $key;
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
     * @return string
     */
    public function convertJSONToType(string $inputVarName = 'input'): string
    {
        $key = $this->key;
        $inner = $this->inner->convertJSONToType($inputVarName);

        return "\$$key = null;\nif (isset(\${$inputVarName}['$key'])) {\n" . $this->indentCode($inner,1) . "\n}";
    }

    /**
     * @param string $outputVarName
     * @return string
     */
    public function convertTypeToJSON(string $outputVarName = 'output'): string
    {
        $key = $this->key;
        $inner = $this->inner->convertTypeToJSON($outputVarName);

        return "if (isset(\$this->$key)) {\n" . $this->indentCode($inner,1) . "\n}";
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
        if (strpos($inner, "|null") === false) {
            $inner .= "|null";
        }

        return $inner;
    }

    /**
     * @param $phpVersion
     * @return string|null
     */
    public function typeHint(int $phpVersion)
    {
        $inner = $this->inner->typeHint($phpVersion);

        if ($phpVersion === 5) {
            return $inner;
        }

        if ($inner === null) {
            return $inner;
        }

        if (strpos($inner, "?") !== 0) {
            $inner = "?" . $inner;
        }

        return $inner;
    }

    /**
     * @return string|null
     */
    public function cloneProperty()
    {
        $key = $this->key();
        $inner = $this->inner->cloneProperty();

        if ($inner !== null) {
            return "if (isset(\$this->$key)) {\n" . $this->indentCode($inner,1) . "\n}";
        }

        return $inner;
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

    /**
     * @return PropertyInterface
     */
    public function unwrap(): PropertyInterface
    {
        return $this->inner;
    }

}