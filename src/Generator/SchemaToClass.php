<?php

namespace Helmich\JsonStructBuilder\Generator;

use Helmich\JsonStructBuilder\Writer\WriterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlock\Tag\ThrowsTag;
use Zend\Code\Generator\DocBlock\Tag\VarTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

class SchemaToClass
{
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

    /**
     * @param GeneratorRequest $in
     * @param OutputInterface  $output
     * @param WriterInterface  $writer
     * @throws GeneratorException
     */
    public function schemaToClass(GeneratorRequest $in, OutputInterface $output, WriterInterface $writer)
    {
        $schemaProperty = new PropertyGenerator("schema", $in->schema, PropertyGenerator::FLAG_PRIVATE | PropertyGenerator::FLAG_STATIC);
        $schemaProperty->setDocBlock(new DocBlockGenerator("Schema used to validate input for creating instances of this class", null, [new VarTag("schema", "array")]));

        $properties = [$schemaProperty];
        $conversions = [];
        $clones = [];
        $methods = [];

        if (!isset($in->schema["properties"])) {
            throw new GeneratorException("cannot generate class for types other than 'object'");
        }

        foreach ($in->schema["properties"] as $key => $definition) {
            $t = isset($definition["type"]) ? $definition["type"] : "any";

            $isObject = $t === "object" || isset($definition["properties"]);
            $isComplex = $isObject || isset($definition["allOf"]) || isset($definition["oneOf"]) || isset($definition["anyOf"]);

            $capitalizedName = strtoupper($key[0]) . substr($key, 1);

            $doc = new DocBlockGenerator();

            $property = new PropertyGenerator($key, isset($definition["default"]) ? $definition["default"] : null, PropertyGenerator::FLAG_PRIVATE);
            $property->setDocBlock($doc);

            $conversion = "\$obj->$key = \$input['$key'];";

            $propertyTypeName = $in->targetClass . $capitalizedName;

            $phpType = $this->defToPHPType($definition, $propertyTypeName);
            $isRequired = isset($in->schema["required"]) && in_array($key, $in->schema["required"]);

            if (!$isRequired && !isset($definition["default"])) {
                $phpType .= "|null";
            }

            $doc->setTag(new VarTag($key, $phpType));

            if (isset($definition["anyOf"])) {
                $definition["oneOf"] = $definition["anyOf"];
            }

            if (isset($definition["oneOf"])) {
                foreach ($definition["oneOf"] as $i => $subDef) {
                    $propertyTypeName = $in->targetClass . $capitalizedName . "Alternative" . ($i + 1);

                    if ((isset($subDef["type"]) && $subDef["type"] === "object") || isset($subDef["properties"])) {
                        $this->schemaToClass($in->withSchema($subDef)->withClass($propertyTypeName), $output, $writer);
                        $conversion .= "\nif ($propertyTypeName::validateInput(\$input['$key'], true)) { \$obj->$key = $propertyTypeName::buildFromInput(\$input['$key']); }";
                    }
                }

                $clones[] = "\$this->$key = clone \$this->$key;";
            }

            if (isset($definition["allOf"])) {
                $propertyTypeName = $in->targetClass . $capitalizedName;
                $combined = $this->buildSchemaIntersect($definition["allOf"]);

                $this->schemaToClass($in->withSchema($combined)->withClass($propertyTypeName), $output, $writer);
                $conversion = "\$obj->$key = $propertyTypeName::buildFromInput(\$input['$key']);";
                $clones[] = "\$this->$key = clone \$this->$key;";
            }

            if ($t === "string") {
                if (isset($definition["format"]) && $definition["format"] == "date-time") {
                    $conversion = "\$obj->$key = new \\DateTime(\$input['$key']);";
                    $clones[] = "\$this->$key = clone \$this->$key;";
                }
            } else if ($t === "integer" || $t === "int") {
                $conversion = "\$obj->$key = (int) \$input['$key'];";
            } else if ($isObject) {
                $this->schemaToClass($in->withSchema($definition)->withClass($propertyTypeName), $output, $writer);
                $conversion = "\$obj->$key = $propertyTypeName::buildFromInput(\$input['$key']);";
                $clones[] = "\$this->$key = clone \$this->$key;";
            } else if ($t === "array") {
                $propertyTypeName = $in->targetClass . $capitalizedName . "Item";

                if ((isset($definition["items"]["type"]) && $definition["items"]["type"] === "object") || isset($definition["items"]["properties"])) {
                    $this->schemaToClass($in->withSchema($definition["items"])->withClass($propertyTypeName), $output, $writer);

                    $conversion = "\$obj->$key = " . 'array_map(function($i) { return ' . $propertyTypeName . '::buildFromInput($i); }, $input["' . $key . '"]);';
                    $clones[] = "\$this->$key = array_map(function(\$i) { return clone \$i; }, \$this->$key);";
                }
            }

            if (!$isRequired) {
                $conversion = "if (isset(\$input['$key'])) {\n    $conversion\n}";
            }

            $typeHint = $phpType;

            if ($isComplex) {
                $typeHint = $in->targetNamespace . "\\" . $typeHint;
            }

            $typeHint = preg_replace('/([a-zA-Z0-9_\\\\]+)\\[\\]/', 'array', $typeHint);
            $typeHint = preg_replace('/^(.*)\\|null$/', '?$1', $typeHint);

            if (strpos($typeHint, '|') !== false) {
                $typeHint = null;
            }

            $getMethod = new MethodGenerator(
                'get' . $capitalizedName,
                [],
                MethodGenerator::FLAG_PUBLIC,
                "return \$this->$key;",
                new DocBlockGenerator(null, null, [new ReturnTag($phpType)])
            );

            $setMethod = new MethodGenerator(
                'with' . $capitalizedName,
                [new ParameterGenerator($key, $in->php5 ? null : $typeHint)],
                MethodGenerator::FLAG_PUBLIC,
                "\$clone = clone \$this;\n\$clone->$key = \$$key;\n\nreturn \$clone;",
                new DocBlockGenerator(null, null, [
                    new ParamTag($key, $phpType),
                    new ReturnTag("self"),
                ])
            );

            if (!$in->php5) {
                if ($typeHint) {
                    $getMethod->setReturnType($typeHint);
                }

                $setMethod->setReturnType("self");
            }

            $methods[] = $getMethod;

            if (!$in->noSetters) {
                $methods[] = $setMethod;
            }

            $conversions[] = $conversion;
            $properties[] = $property;
        }

        $buildMethod = new MethodGenerator(
            'buildFromInput',
            [new ParameterGenerator("input", "array")],
            MethodGenerator::FLAG_PUBLIC | MethodGenerator::FLAG_STATIC,
            'static::validateInput($input);' . "\n\n" .
            '$obj = new static;' . "\n" .
            join("\n", $conversions) . "\n\n" .
            'return $obj;',
            new DocBlockGenerator(
                "Builds a new instance from an input array", null, [
                    new ParamTag("input", ["array"], "Input data"),
                    new ReturnTag([$in->targetClass], "Created instance"),
                    new ThrowsTag("\\InvalidArgumentException"),
                ]
            )
        );

        $validateMethod = new MethodGenerator(
            'validateInput',
            [
                new ParameterGenerator("input", $in->php5 ? null : "array"),
                new ParameterGenerator("return", $in->php5 ? null : "bool", false),
            ],
            MethodGenerator::FLAG_PUBLIC | MethodGenerator::FLAG_STATIC,
            '$validator = new \\JsonSchema\\Validator();' . "\n" .
            '$validator->validate($input, static::$schema);' . "\n\n" .
            'if (!$validator->isValid() && !$return) {' . "\n" .
            '    $errors = array_map(function($e) {' . "\n" .
            '        return $e["property"] . ": " . $e["message"];' . "\n" .
            '    }, $validator->getErrors());' . "\n" .
            '    throw new \\InvalidArgumentException(join(", ", $errors));' . "\n" .
            '}' . "\n\n" .
            'return $validator->isValid();',
            new DocBlockGenerator(
                "Validates an input array", null, [
                    new ParamTag("input", ["array"], "Input data"),
                    new ParamTag("return", ["bool"], "Return instead of throwing errors"),
                    new ReturnTag(["bool"], "Validation result"),
                    new ThrowsTag("\\InvalidArgumentException"),
                ]
            )
        );

        $cloneMethod = new MethodGenerator(
            '__clone',
            [],
            MethodGenerator::FLAG_PUBLIC,
            join("\n", $clones)
        );

        if (!$in->php5) {
            $buildMethod->setReturnType($in->targetNamespace . "\\" . $in->targetClass);
            $validateMethod->setReturnType("bool");
        }

        $methods[] = $buildMethod;
        $methods[] = $validateMethod;
        $methods[] = $cloneMethod;

        $cls = new ClassGenerator(
            $in->targetClass,
            $in->targetNamespace,
            null,
            null,
            [],
            $properties,
            $methods,
            null
        );

        $file = new FileGenerator([
            "classes" => [$cls],
        ]);

        $content = $file->generate();

        // Do some corrections because the Zend code generation library is stupid.
        $content = preg_replace('/ : \\\\self/', ' : self', $content);
        $content = preg_replace('/\\\\'.preg_quote($in->targetNamespace).'\\\\/', '', $content);

        $writer->writeFile($in->targetDirectory . '/' . $in->targetClass . '.php', $content);
    }

    private function defToPHPType(array $def, $propertyTypeName = "")
    {
        $t = isset($def["type"]) ? $def["type"] : "any";

        if (isset($def["anyOf"])) {
            $def["oneOf"] = $def["anyOf"];
        }

        if (isset($def["oneOf"])) {
            $types = [];

            foreach ($def["oneOf"] as $i => $subDef) {
                $name = $propertyTypeName . "Alternative" . ($i + 1);
                $types[] = $this->defToPHPType($subDef, $name);
            }

            return join("|", $types);
        }

        if (isset($def["allOf"])) {
            return $propertyTypeName;
        }

        if ($t === "string") {
            if (isset($def["format"]) && $def["format"] == "date-time") {
                return "\\DateTime";
            }

            return "string";
        } else if ($t === "object" || isset($definition["properties"])) {
            return $propertyTypeName;
        } else if ($t === "array") {
            return $this->defToPHPType($def["items"], $propertyTypeName . "Item") . "[]";
        } else if ($t === "integer" || $t === "int") {
            return "int";
        } else if ($t === "number") {
            if (isset($def["format"]) && $def["format"] === "integer") {
                return "int";
            }

            return "float";
        } else if ($t === "object" || isset($def["properties"])) {
            return $propertyTypeName;
        }

        return "mixed";
    }
}