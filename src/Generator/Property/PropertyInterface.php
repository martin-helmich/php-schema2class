<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\SchemaToClass;

interface PropertyInterface
{
    /**
     * @param array $schema
     * @return bool
     */
    public static function canHandleSchema(array $schema): bool;

    /**
     * @return array
     */
    public function schema(): array;

    /**
     * @return string
     */
    public function key(): string;

    /**
     * @return bool
     */
    public function isComplex(): bool;

    /**
     * @param string $inputVarName
     * @param bool   $object
     * @return string
     */
    public function convertJSONToType(string $inputVarName = 'input', bool $object = false): string;

    /**
     * @param string $outputVarName
     * @return string
     */
    public function convertTypeToJSON(string $outputVarName = 'output'): string;

    /**
     * @param SchemaToClass $generator
     * @return void
     */
    public function generateSubTypes(SchemaToClass $generator): void;

    /**
     * @return string
     */
    public function typeAnnotation(): string;

    /**
     * @param string $phpVersion
     * @return string|null
     */
    public function typeHint(string $phpVersion): ?string;

    /**
     * @param string $expr
     * @return string
     */
    public function generateTypeAssertionExpr(string $expr): string;

    /**
     * @param string $expr
     * @return string
     */
    public function generateInputAssertionExpr(string $expr): string;

    /**
     * @param string $expr
     * @param bool   $asserted
     * @return string
     */
    public function generateInputMappingExpr(string $expr, bool $asserted = false): string;

    /**
     * @param string $expr
     * @return string
     */
    public function generateOutputMappingExpr(string $expr): string;

    /**
     * @param string $expr
     * @return string
     */
    public function generateCloneExpr(string $expr): string;

    /**
     * @return string|null
     */
    public function cloneProperty(): ?string;

}
