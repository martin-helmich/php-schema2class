<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Codegen\PropertyGenerator;
use Helmich\Schema2Class\Generator\Property\CodeFormatting;
use Helmich\Schema2Class\Generator\Property\OptionalPropertyDecorator;
use Helmich\Schema2Class\Generator\Property\PropertyCollection;
use Helmich\Schema2Class\Generator\Property\PropertyInterface;
use Laminas\Code\Generator\DocBlock\Tag\GenericTag;
use Laminas\Code\Generator\DocBlock\Tag\ParamTag;
use Laminas\Code\Generator\DocBlock\Tag\ReturnTag;
use Laminas\Code\Generator\DocBlock\Tag\ThrowsTag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;

class Generator
{
    use CodeFormatting;

    private GeneratorRequest $generatorRequest;

    public function __construct(GeneratorRequest $generatorRequest)
    {
        $this->generatorRequest = $generatorRequest;
    }

    /**
     * @param PropertyCollection $properties
     * @return PropertyGenerator[]
     */
    public function generateProperties(PropertyCollection $properties): array
    {
        $propertyGenerators = [];

        foreach ($properties as $property) {
            $schema = $property->schema();
            $isOptional = false;
            $prop = new PropertyGenerator(
                $property->key(),
                isset($schema["default"]) ? $schema["default"] : null,
                PropertyGenerator::FLAG_PRIVATE
            );

            if ($property instanceof OptionalPropertyDecorator) {
                $isOptional = true;
                if (isset($schema["default"]) && $schema["default"] !== null) {
                    $property = $property->unwrap();
                }
            }

            $prop->setDocBlock(new DocBlockGenerator(
                isset($schema["description"]) ? $schema["description"] : null,
                null,
                [new GenericTag("var", $property->typeAnnotation())]
            ));

            $typeHint = $property->typeHint($this->generatorRequest->getTargetPHPVersion());
            if ($this->generatorRequest->isAtLeastPHP("7.4") && $typeHint) {
                $prop->setTypeHint($typeHint);
            }

            if (!$isOptional) {
                $prop->omitDefaultValue(true);
            }

            $propertyGenerators[] = $prop;
        }

        return $propertyGenerators;
    }

    /**
     * @param PropertyCollection $properties
     * @return MethodGenerator
     */
    public function generateBuildMethod(PropertyCollection $properties): MethodGenerator
    {
        $requiredProperties = $properties->filterRequired();
        $optionalProperties = $properties->filterOptional();

        $constructorParams = [];
        $assignments = [];

        foreach ($requiredProperties as $requiredProperty) {
            $constructorParams[] = '$' . $requiredProperty->key();
        }

        foreach ($optionalProperties as $optionalProperty) {
            $assignments[] = "\$obj->{$optionalProperty->key()} = \${$optionalProperty->key()};";
        }

        $inputVarName = 'input';
        if ($properties->hasPropertyWithKey($inputVarName)) {
            $i = 2;
            do {
                $inputVarName = 'input' . $i;
            } while ($properties->hasPropertyWithKey($inputVarName));
        }

        $method = new MethodGenerator(
            'buildFromInput',
            [new ParameterGenerator($inputVarName, "array")],
            MethodGenerator::FLAG_PUBLIC | MethodGenerator::FLAG_STATIC,
            "static::validateInput(\$$inputVarName);\n\n" .
            $properties->generateJSONToTypeConversionCode($inputVarName) . "\n\n" .
            '$obj = new self(' . join(", ", $constructorParams) . ');' . "\n" .
            join("\n", $assignments) . "\n" .
            'return $obj;',
            new DocBlockGenerator(
                "Builds a new instance from an input array", null, [
                    new ParamTag($inputVarName, ["array"], "Input data"),
                    new ReturnTag([$this->generatorRequest->getTargetClass()], "Created instance"),
                    new ThrowsTag("\\InvalidArgumentException"),
                ]
            )
        );

        if ($this->generatorRequest->isAtLeastPHP("7.0")) {
            $method->setReturnType($this->generatorRequest->getTargetNamespace() . "\\" . $this->generatorRequest->getTargetClass());
        }

        return $method;
    }

    /**
     * @param PropertyCollection $properties
     * @return MethodGenerator
     */
    public function generateToJSONMethod(PropertyCollection $properties): MethodGenerator
    {
        $method = new MethodGenerator(
            'toJson',
            [],
            MethodGenerator::FLAG_PUBLIC,
            '$output = [];' . "\n" .
            $properties->generateTypeToJSONConversionCode('output') . "\n\n" .
            'return $output;',
            new DocBlockGenerator(
                "Converts this object back to a simple array that can be JSON-serialized", null, [
                    new ReturnTag(["array"], "Converted array"),
                ]
            )
        );

        if ($this->generatorRequest->isAtLeastPHP("7.0")) {
            $method->setReturnType("array");
        }

        return $method;
    }

    /**
     * @return MethodGenerator
     */
    public function generateValidateMethod(): MethodGenerator
    {
        $method = new MethodGenerator(
            'validateInput',
            [
                new ParameterGenerator("input", $this->generatorRequest->isAtLeastPHP("7.0") ? "array" : null),
                new ParameterGenerator("return", $this->generatorRequest->isAtLeastPHP("7.0") ? "bool" : null, false),
            ],
            MethodGenerator::FLAG_PUBLIC | MethodGenerator::FLAG_STATIC,
            '$validator = new \\JsonSchema\\Validator();' . "\n" .
            '$validator->validate($input, static::$schema);' . "\n\n" .
            'if (!$validator->isValid() && !$return) {' . "\n" .
            ($this->generatorRequest->isAtLeastPHP("7.0") ?
                '    $errors = array_map(function(array $e): string {' . "\n" :
                '    $errors = array_map(function($e) {' . "\n")  .
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

        if ($this->generatorRequest->isAtLeastPHP("7.0")) {
            $method->setReturnType("bool");
        }

        return $method;
    }

    /**
     * @param PropertyCollection $properties
     * @return MethodGenerator
     */
    public function generateCloneMethod(PropertyCollection $properties): MethodGenerator
    {
        $clones = [];

        foreach ($properties as $property) {
            $c = $property->cloneProperty();
            if ($c !== null) {
                $clones[] = $c;
            }
        }

        return new MethodGenerator(
            '__clone',
            [],
            MethodGenerator::FLAG_PUBLIC,
            join("\n", $clones)
        );
    }

    /**
     * @param PropertyCollection $properties
     * @return MethodGenerator[]
     */
    public function generateGetterMethods(PropertyCollection $properties): array
    {
        $methods = [];

        foreach ($properties as $property) {
            $methods[] = $this->generateGetterMethod($property);
        }

        return $methods;
    }

    /**
     * @param PropertyInterface $property
     * @return MethodGenerator
     */
    public function generateGetterMethod(PropertyInterface $property): MethodGenerator
    {
        if (isset($property->schema()["default"]) && $property instanceof OptionalPropertyDecorator) {
            $property = $property->unwrap();
        }

        $key = $property->key();
        $capitalizedName = $this->capitalize($key);
        $annotatedType = $property->typeAnnotation();

        $getMethod = new MethodGenerator(
            'get' . $capitalizedName,
            [],
            MethodGenerator::FLAG_PUBLIC,
            "return \$this->$key;",
            new DocBlockGenerator(null, null, [new ReturnTag($annotatedType)])
        );

        if ($this->generatorRequest->isAtLeastPHP("7.0")) {
            $typeHint = $property->typeHint($this->generatorRequest->getTargetPHPVersion());
            if ($typeHint) {
                $getMethod->setReturnType($typeHint);

                if ($typeHint[0] === '?') {
                    $getMethod->setBody("return isset(\$this->${key}) ? \$this->${key} : null;");
                }
            }
        }

        return $getMethod;
    }

    /**
     * @param PropertyCollection $properties
     * @return MethodGenerator[]
     */
    public function generateSetterMethods(PropertyCollection $properties): array
    {
        $methods = [];

        foreach ($properties as $property) {
            $methods[] = $this->generateSetterMethod($property);

            if ($property instanceof OptionalPropertyDecorator) {
                $methods[] = $this->generateUnsetterMethod($property);
            }
        }

        return $methods;
    }

    public function generateSetterMethod(PropertyInterface $property): MethodGenerator
    {
        $key = $property->key();
        $capitalizedName = $this->capitalize($key);

        $requiredProperty = ($property instanceof OptionalPropertyDecorator) ? $property->unwrap() : $property;

        $annotatedType = $requiredProperty->typeAnnotation();
        $typeHint = $requiredProperty->typeHint($this->generatorRequest->getTargetPHPVersion());

        if ($property->isComplex()) {
            $setterValidation = "";
        } else {
            $setterValidation = "\$validator = new \JsonSchema\Validator();
\$validator->validate(\$$key, static::\$schema['properties']['$key']);
if (!\$validator->isValid()) {
    throw new \InvalidArgumentException(\$validator->getErrors()[0]['message']);
}

";
        }

        $setMethod = new MethodGenerator(
            'with' . $capitalizedName,
            [new ParameterGenerator($key, $typeHint)],
            MethodGenerator::FLAG_PUBLIC,
            $setterValidation . "\$clone = clone \$this;
\$clone->$key = \$$key;

return \$clone;",
            new DocBlockGenerator(null, null, [
                new ParamTag($key, [str_replace("|null", "", $annotatedType)]),
                new ReturnTag("self"),
            ])
        );

        if ($this->generatorRequest->isAtLeastPHP("7.0")) {
            $setMethod->setReturnType("self");
        }

        return $setMethod;
    }

    /**
     * @param PropertyInterface $property
     * @return MethodGenerator
     */
    public function generateUnsetterMethod(PropertyInterface $property): MethodGenerator
    {
        $key = $property->key();
        $capitalizedName = $this->capitalize($key);

        $unsetMethod = new MethodGenerator(
            'without' . $capitalizedName,
            [],
            MethodGenerator::FLAG_PUBLIC,
            "\$clone = clone \$this;
unset(\$clone->$key);

return \$clone;",
            new DocBlockGenerator(null, null, [
                new ReturnTag("self"),
            ])
        );

        if ($this->generatorRequest->isAtLeastPHP("7.0")) {
            $unsetMethod->setReturnType("self");
        }

        return $unsetMethod;
    }

    public function generateConstructor(PropertyCollection $properties): MethodGenerator
    {
        $params = [];
        $tags = [];
        $assignments = [];

        $requiredProperties = $properties->filterRequired();

        foreach ($requiredProperties as $requiredProperty) {
            $params[] = new ParameterGenerator(
                $requiredProperty->key(),
                $requiredProperty->typeHint($this->generatorRequest->getTargetPHPVersion())
            );

            $tags[] = new ParamTag(
                $requiredProperty->key(),
                [$requiredProperty->typeAnnotation()]
            );

            $assignments[] = "\$this->{$requiredProperty->key()} = \${$requiredProperty->key()};";
        }

        return new MethodGenerator(
            "__construct",
            $params,
            MethodGenerator::FLAG_PUBLIC,
            join("\n", $assignments),
            new DocBlockGenerator("", "", $tags)
        );
    }
}
