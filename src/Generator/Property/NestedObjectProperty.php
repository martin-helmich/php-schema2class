<?php
namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\SchemaToClass;

class NestedObjectProperty extends AbstractPropertyInterface
{
    use TypeConvert;

    public static function canHandleSchema(array $schema)
    {
        return isset($schema["type"]) && $schema["type"] === "object"
            || isset($schema["properties"]);
    }

    public function isComplex()
    {
        return true;
    }

    public function convertJSONToType($inputVarName = 'input')
    {
        $key = $this->key;

        return "\$$key = {$this->subTypeName()}::buildFromInput(\${$inputVarName}['$key']);";
    }

    public function convertTypeToJSON($outputVarName = 'output')
    {
        $key = $this->key;

        return "\${$outputVarName}['$key'] = \$this->{$key}->toJson();";
    }

    public function cloneProperty()
    {
        $key = $this->key;

        return "\$this->$key = clone \$this->$key;";
    }

    /**
     * @param SchemaToClass    $generator
     * @throws \Helmich\Schema2Class\Generator\GeneratorException
     */
    public function generateSubTypes(SchemaToClass $generator)
    {
        $generator->schemaToClass(
            $this->generatorRequest
                ->withSchema($this->schema)
                ->withClass($this->subTypeName())
        );
    }

    public function typeAnnotation()
    {
        return $this->subTypeName();
    }

    public function typeHint($phpVersion)
    {
        return "\\" . $this->generatorRequest->getTargetNamespace() . "\\" . $this->subTypeName();
    }

    private function subTypeName()
    {
        return $this->generatorRequest->getTargetClass() . $this->capitalizedName;
    }

}
