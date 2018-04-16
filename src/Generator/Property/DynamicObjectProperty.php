<?php
namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\SchemaToClass;

class DynamicObjectProperty extends AbstractPropertyInterface
{
    use TypeConvert;

    public static function canHandleSchema(array $schema)
    {
        return isset($schema["type"]) && $schema["type"] === "object" ||
            (isset($schema["patternProperties"]) || isset($schema["additionalProperties"]));
    }

    public function isComplex()
    {
        return true;
    }

    public function convertJSONToType($inputVarName = 'input')
    {
        $key = $this->key;
        $code = "\$$key = [];\n" .
            "foreach (\$input['$key'] as \$k => \$v) {\n";

        $i = 1;
        $j = 0;

        if (isset($this->schema["patternProperties"])) {
            foreach ($this->schema["patternProperties"] as $k => $subDef) {
                if ((isset($subDef["type"]) && $subDef["type"] === "object") || isset($subDef["properties"])) {

                    $type = $this->subTypeName() . "Pattern" . $i;
                    $pattern = var_export($k, true);

                    $code .= "    " . ($j > 0 ? "else " : "") . "if (preg_match($pattern, \$k)) {\n" .
                        "        \${$key}[\$k] = {$type}::buildFromInput(\$v);\n" .
                        "    }\n";

                    $j++;
                }

                $i++;
            }
        }

        if (isset($this->schema["additionalProperties"])) {
            $subDef = $this->schema["additionalProperties"];
            if ((isset($subDef["type"]) && $subDef["type"] === "object") || isset($subDef["properties"])) {
                $type = $this->subTypeName() . "Additional";

                if ($j > 0) {
                    $code .= "    else {\n" .
                        "        \${$key}[\$k] = {$type}::buildFromInput(\$v);\n" .
                        "    }";
                } else {
                    $code .= "    \${$key}[\$k] = {$type}::buildFromInput(\$v);\n";
                }
            }
        }

        $code .= "}";

        return $code;
    }

    public function convertTypeToJSON($outputVarName = 'output')
    {
        $key = $this->key;

        return "\${$outputVarName}['$key'] = [];\n" .
            "foreach (\$this->$key as \$k => \$v) {\n" .
            "   \${$outputVarName}['$key'][\$k] = \$v->toJson();\n" .
            "}";
    }

    public function cloneProperty()
    {
        $key = $this->key;

        return "\$this->$key = [];\n" .
            "foreach (\$this->$key as \$k => \$v) {\n" .
            "   \$this->{$key}[\$k] = clone \$v;\n" .
            "}";
    }

    /**
     * @param SchemaToClass    $generator
     * @throws \Helmich\Schema2Class\Generator\GeneratorException
     */
    public function generateSubTypes(SchemaToClass $generator)
    {
        $i = 1;

        if (isset($this->schema["patternProperties"])) {
            foreach ($this->schema["patternProperties"] as $k => $subDef) {
                if ((isset($subDef["type"]) && $subDef["type"] === "object") || isset($subDef["properties"])) {
                    $req = $this->ctx->request
                        ->withSchema($subDef)
                        ->withClass($this->subTypeName() . "Pattern" . $i);

                    $generator->schemaToClass($req, $this->ctx->output, $this->ctx->writer);
                }

                $i ++;
            }
        }

        if (isset($this->schema["additionalProperties"])) {
            $subDef = $this->schema["additionalProperties"];
            if ((isset($subDef["type"]) && $subDef["type"] === "object") || isset($subDef["properties"])) {
                $req = $this->ctx->request
                    ->withSchema($subDef)
                    ->withClass($this->subTypeName($i) . "Additional");

                $generator->schemaToClass($req, $this->ctx->output, $this->ctx->writer);
            }
        }
    }

    public function typeAnnotation()
    {
        return "array";
    }

    public function typeHint($phpVersion)
    {
        return "array";
    }

    private function subTypeName($idx = 0)
    {
        return $this->ctx->request->targetClass . $this->capitalizedName;
    }

}