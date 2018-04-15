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

        return "\$$key = {$this->subTypeName()}::buildFromInput(\$input['$key']);";
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
        $req = $this->ctx->request
            ->withSchema($this->schema)
            ->withClass($this->subTypeName());

        $generator->schemaToClass($req, $this->ctx->output, $this->ctx->writer);
    }

    public function typeAnnotation()
    {
        return $this->subTypeName();
    }

    public function typeHint($phpVersion)
    {
        return "\\" . $this->ctx->request->targetNamespace . "\\" . $this->subTypeName();
    }

    private function subTypeName()
    {
        return $this->ctx->request->targetClass . $this->capitalizedName;
    }

}