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
     * @return string
     */
    public function convertJSONToType(string $inputVarName = 'input'): string;

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
     * @param int $phpVersion
     * @return string|null
     */
    public function typeHint(int $phpVersion);

    /**
     * @return string|null
     */
    public function cloneProperty();

}