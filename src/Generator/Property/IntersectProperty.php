<?php
namespace Helmich\JsonStructBuilder\Generator\Property;

use Helmich\JsonStructBuilder\Generator\SchemaToClass;

class IntersectProperty extends AbstractPropertyInterface
{
    use TypeConvert;

    public static function canHandleSchema(array $schema)
    {
        return isset($schema["allOf"]);
    }

    public function isComplex()
    {
        return true;
    }

    public function convertJSONToType($inputVarName = 'input')
    {
        $key = $this->key;

        return "\$obj->$key = {$this->subTypeName()}::buildFromInput(\$input['$key']);";
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
     * @throws \Helmich\JsonStructBuilder\Generator\GeneratorException
     */
    public function generateSubTypes(SchemaToClass $generator)
    {
        $propertyTypeName = $this->subTypeName();
        $combined = $this->buildSchemaIntersect($this->schema["allOf"]);

        $req = $this->ctx->request
            ->withSchema($combined)
            ->withClass($propertyTypeName);

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

    private function buildSchemaUnion(array $schemas)
    {
        $combined = [
            "required" => [],
            "properties" => [],
        ];

        foreach ($schemas as $i => $schema) {
            $required = isset($schema["required"]) ? $schema["required"] : [];

            if ($i === 0) {
                $combined["required"] = $required;
            } else {
                foreach ($combined["required"] as $j => $req) {
                    if (!in_array($req, $required)) {
                        unset($combined["required"][$j]);
                    }
                }
            }

            if (isset($schema["properties"])) {
                foreach ($schema["properties"] as $name => $def) {
                    $combined["properties"][$name] = $def;
                }
            }
        }

        return $combined;
    }

    private function buildSchemaIntersect(array $schemas)
    {
        $combined = [
            "required" => [],
            "properties" => [],
        ];

        foreach ($schemas as $schema) {
            if (isset($schema["oneOf"])) {
                $schema = $this->buildSchemaUnion($schema["oneOf"]);
            }

            if (isset($schema["anyOf"])) {
                $schema = $this->buildSchemaUnion($schema["anyOf"]);
            }

            if (isset($schema["required"])) {
                $combined["required"] = array_unique(array_merge($combined["required"], $schema["required"]));
            }

            if (isset($schema["properties"])) {
                foreach ($schema["properties"] as $name => $def) {
                    $combined["properties"][$name] = $def;
                }
            }
        }

        return $combined;
    }

}