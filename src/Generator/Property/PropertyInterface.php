<?php

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\SchemaToClass;

interface PropertyInterface
{
    /**
     * @param array $schema
     * @return bool
     */
    public static function canHandleSchema(array $schema);

    /**
     * @return array
     */
    public function schema();

    /**
     * @return string
     */
    public function key();

    /**
     * @return bool
     */
    public function isComplex();

    /**
     * @param string $inputVarName
     * @return string
     */
    public function convertJSONToType($inputVarName = 'input');

    /**
     * @param string $outputVarName
     * @return string
     */
    public function convertTypeToJSON($outputVarName = 'output');

    /**
     * @param SchemaToClass $generator
     * @return void
     */
    public function generateSubTypes(SchemaToClass $generator);

    /**
     * @return string
     */
    public function typeAnnotation();

    /**
     * @param int $phpVersion
     * @return string|null
     */
    public function typeHint($phpVersion);

    /**
     * @return string|null
     */
    public function cloneProperty();

}