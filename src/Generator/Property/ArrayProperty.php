<?php
namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\SchemaToClass;

class ArrayProperty extends AbstractPropertyInterface
{
    use TypeConvert;

    public static function canHandleSchema(array $schema)
    {
        return isset($schema["type"]) && $schema["type"] === "array";
    }

    public function isComplex()
    {
        return $this->isObjectArray();
    }

    public function convertJSONToType($inputVarName = 'input')
    {
        $key = $this->key;

        if ($this->isObjectArray()) {
            return "\$$key = " . 'array_map(function($i) { return ' . $this->subTypeName() . "::buildFromInput(\$i); }, \${$inputVarName}['$key']);";
        }

        return parent::convertJSONToType($inputVarName);
    }

    public function convertTypeToJSON($outputVarName = 'output')
    {
        $key = $this->key;
        $st = $this->subTypeName();

        if ($this->isObjectArray()) {
            return "\${$outputVarName}['$key'] = array_map(function($st \$i) { return \$i->toJson(); }, \$this->$key);";
        }

        return parent::convertTypeToJSON($outputVarName);
    }

    public function cloneProperty()
    {
        $key = $this->key;
        $st = $this->subTypeName();

        if ($this->isObjectArray()) {
            return "\$this->$key = array_map(function($st \$i) { return clone \$i; }, \$this->$key);";
        }

        $key = $this->key;
        return "\$this->$key = clone \$this->$key;";
    }

    /**
     * @param SchemaToClass    $generator
     * @throws \Helmich\Schema2Class\Generator\GeneratorException
     */
    public function generateSubTypes(SchemaToClass $generator)
    {
        $def = $this->schema;

        if ($this->isObjectArray()) {
            $generator->schemaToClass(
                $this->generatorRequest->withSchema($def["items"])->withClass($this->subTypeName())
            );
        }
    }

    public function typeAnnotation()
    {
        if ($this->isObjectArray()) {
            return $this->subTypeName() . "[]";
        }

        if (isset($this->schema["items"])) {
            list ($annot, $hint) = $this->phpPrimitiveForSchemaType($this->schema["items"]);
            return $annot . "[]";
        }

        return "array";
    }

    public function typeHint($phpVersion)
    {
        return "array";
    }

    private function subTypeName()
    {
        return $this->generatorRequest->getTargetClass() . $this->capitalizedName . 'Item';
    }

    private function isObjectArray()
    {
        return
            (isset($this->schema["items"]["type"]) && $this->schema["items"]["type"] === "object")
            || isset($this->schema["items"]["properties"]);
    }

}
