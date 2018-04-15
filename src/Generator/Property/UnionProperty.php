<?php
namespace Helmich\JsonStructBuilder\Generator\Property;

use Helmich\JsonStructBuilder\Generator\GeneratorContext;
use Helmich\JsonStructBuilder\Generator\SchemaToClass;

class UnionProperty extends AbstractPropertyInterface
{
    use TypeConvert;

    public static function canHandleSchema(array $schema)
    {
        return isset($schema["oneOf"]) || isset($schema["anyOf"]);
    }

    public function __construct($key, array $schema, GeneratorContext $ctx)
    {
        if (isset($schema["anyOf"])) {
            $schema["oneOf"] = $schema["anyOf"];
            unset($schema["anyOf"]);
        }

        parent::__construct($key, $schema, $ctx);
    }

    public function isComplex()
    {
        return true;
    }

    public function convertJSONToType($inputVarName = 'input')
    {
        $conversions = [];
        $def = $this->schema;
        $key = $this->key;

        foreach ($def["oneOf"] as $i => $subDef) {
            $propertyTypeName = $this->subTypeName($i);

            if ((isset($subDef["type"]) && $subDef["type"] === "object") || isset($subDef["properties"])) {
                $conversions[] = ($i > 0 ? "else " : "") . "if ($propertyTypeName::validateInput(\$input['$key'], true)) {\n    \$$key = $propertyTypeName::buildFromInput(\$input['$key']);\n}";
            }
        }

        $conversions[] = "else {\n    \$$key = \${$inputVarName}['$key'];\n}";

        return str_replace("}\nelse", "} else", join("\n", $conversions));
    }

    public function convertTypeToJSON($outputVarName = 'output')
    {
        $conversions = [];
        $def = $this->schema;
        $key = $this->key;

        foreach ($def["oneOf"] as $i => $subDef) {
            $propertyTypeName = $this->subTypeName($i);

            if ((isset($subDef["type"]) && $subDef["type"] === "object") || isset($subDef["properties"])) {
                $conversions[] = "if (\$this instanceof $propertyTypeName) {\n    \$output['$key'] = \$this->{$key}->toJson();\n}";
            }
        }

        return join("\n", $conversions);
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
        $def = $this->schema;

        foreach ($def["oneOf"] as $i => $subDef) {
            $propertyTypeName = $this->subTypeName($i);

            if ((isset($subDef["type"]) && $subDef["type"] === "object") || isset($subDef["properties"])) {
                $req = $this->ctx->request
                    ->withSchema($subDef)
                    ->withClass($propertyTypeName);

                $generator->schemaToClass($req, $this->ctx->output, $this->ctx->writer);
            }
        }
    }

    public function typeAnnotation()
    {
        $types = [];
        $def = $this->schema;

        foreach ($def["oneOf"] as $i => $subDef) {
            $propertyTypeName = $this->subTypeName($i);
            if ((isset($subDef["type"]) && $subDef["type"] === "object") || isset($subDef["properties"])) {
                $types[] = $propertyTypeName;
            } else {
                $types[] = $this->phpPrimitiveForSchemaType($subDef)[0];
            }
        }

        return join("|", $types);
    }

    public function typeHint($phpVersion)
    {
        return null;
    }

    private function subTypeName($idx = 0)
    {
        return $this->ctx->request->targetClass . $this->capitalizedName . "Alternative" . ($idx + 1);
    }

}