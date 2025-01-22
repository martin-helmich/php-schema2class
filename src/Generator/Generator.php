<?php

declare(strict_types=1);

namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Codegen\PropertyGenerator;
use Helmich\Schema2Class\Generator\Property\CodeFormatting;
use Helmich\Schema2Class\Generator\Property\DefaultPropertyDecorator;
use Helmich\Schema2Class\Generator\Property\OptionalPropertyDecorator;
use Helmich\Schema2Class\Generator\Property\PropertyCollection;
use Helmich\Schema2Class\Generator\Property\PropertyCollectionFilterFactory;
use Helmich\Schema2Class\Generator\Property\PropertyInterface;
use Helmich\Schema2Class\Util\StringUtils;
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
            $schema     = $property->schema();
            $isOptional = false;
            $prop       = new PropertyGenerator(
                $property->name(),
                $property->formatValue($schema["default"] ?? null),
                PropertyGenerator::FLAG_PRIVATE
            );

            if ($property instanceof OptionalPropertyDecorator || $property instanceof DefaultPropertyDecorator) {
                $isOptional = true;
                if (isset($schema["default"])) {
                    $property = $property->unwrap();
                }
            }

            $tags = [new GenericTag("var", trim($property->typeAnnotation()))];
            if (PropertyQuery::isDeprecated($property)) {
                $tags[] = new GenericTag("deprecated");
            }

            $docBlock = new DocBlockGenerator(
                $schema["description"] ?? null,
                null,
                $tags
            );
            $docBlock->setWordWrap(false);

            $prop->setDocBlock($docBlock);

            $typeHint = $property->typeHint($this->generatorRequest->getTargetPHPVersion());
            if ($this->generatorRequest->isAtLeastPHP("7.4") && $typeHint !== null) {
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
        $requiredProperties = $properties->filter(PropertyCollectionFilterFactory::required());
        $optionalProperties = $properties->filter(PropertyCollectionFilterFactory::optional());

        $constructorParams = [];
        $assignments       = [];

        foreach ($requiredProperties as $requiredProperty) {
            $constructorParams[] = '$' . $requiredProperty->name();
        }

        foreach ($optionalProperties as $optionalProperty) {
            $assignments[] = "\$obj->{$optionalProperty->name()} = \${$optionalProperty->name()};";
        }

        $inputVarName = 'input';
        if ($properties->hasPropertyWithKey($inputVarName)) {
            $i = 2;
            do {
                $inputVarName = 'input' . $i;
                $i++;
            } while ($properties->hasPropertyWithKey($inputVarName));
        }

        $paramType = null;
        if ($this->generatorRequest->isAtLeastPHP("8.0")) {
            $paramType = "array|object";
        }

        $validationParam = new ParameterGenerator(
            name: "validate",
            type: "bool",
            defaultValue: true,
        );

        $docBlock = new DocBlockGenerator(
            "Builds a new instance from an input array",
            null,
            [
                new ParamTag($inputVarName, ["array|object"], "Input data"),
                new ParamTag("validate", ["bool"], "Set this to false to skip validation; use at own risk"),
                new ReturnTag([$this->generatorRequest->getTargetClass()], "Created instance"),
                new ThrowsTag("\\InvalidArgumentException"),
            ]
        );
        $docBlock->setWordWrap(false);

        $method = new MethodGenerator(
            'buildFromInput',
            [new ParameterGenerator($inputVarName, $paramType), $validationParam],
            MethodGenerator::FLAG_PUBLIC | MethodGenerator::FLAG_STATIC,
            "\$$inputVarName = is_array(\$$inputVarName) ? \\JsonSchema\\Validator::arrayToObjectRecursive(\$$inputVarName) : \$$inputVarName;\n" .
            "if (\$validate) {\n" .
            "    static::validateInput(\$$inputVarName);\n" .
            "}\n\n" .
            $properties->generateJSONToTypeConversionCode($inputVarName, object: true) . "\n\n" .
            '$obj = new self(' . join(", ", $constructorParams) . ');' . "\n" .
            join("\n", $assignments) . "\n" .
            'return $obj;',
            $docBlock
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
        $docBlock = new DocBlockGenerator(
            "Converts this object back to a simple array that can be JSON-serialized",
            null,
            [new ReturnTag(["array"], "Converted array")]
        );
        $docBlock->setWordWrap(false);

        $method = new MethodGenerator(
            'toJson',
            [],
            MethodGenerator::FLAG_PUBLIC,
            '$output = [];' . "\n" .
            $properties->generateTypeToJSONConversionCode('output') . "\n\n" .
            'return $output;',
            $docBlock
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
        $docBlock = new DocBlockGenerator(
            "Validates an input array",
            null,
            [
                new ParamTag("input", ["array|object"], "Input data"),
                new ParamTag("return", ["bool"], "Return instead of throwing errors"),
                new ReturnTag(["bool"], "Validation result"),
                new ThrowsTag("\\InvalidArgumentException"),
            ]
        );
        $docBlock->setWordWrap(false);

        $newValidatorClassExpr = $this->generatorRequest->getOptions()->getNewValidatorClassExpr();

        $method = new MethodGenerator(
            'validateInput',
            [
                new ParameterGenerator("input", $this->generatorRequest->isAtLeastPHP("8.0") ? "array|object" : null),
                new ParameterGenerator("return", $this->generatorRequest->isAtLeastPHP("7.0") ? "bool" : null, false),
            ],
            MethodGenerator::FLAG_PUBLIC | MethodGenerator::FLAG_STATIC,
            '$validator = ' . $newValidatorClassExpr . ';' . "\n" .
            '$input = is_array($input) ? \\JsonSchema\\Validator::arrayToObjectRecursive($input) : $input;' . "\n" .
            '$validator->validate($input, self::$schema);' . "\n\n" .
            'if (!$validator->isValid() && !$return) {' . "\n" .
            ($this->generatorRequest->isAtLeastPHP("7.0") ?
                '    $errors = array_map(function(array $e): string {' . "\n" :
                '    $errors = array_map(function($e) {' . "\n") .
            '        return $e["property"] . ": " . $e["message"];' . "\n" .
            '    }, $validator->getErrors());' . "\n" .
            '    throw new \\InvalidArgumentException(join(", ", $errors));' . "\n" .
            '}' . "\n\n" .
            'return $validator->isValid();',
            $docBlock
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

        $properties = $properties->filter(PropertyCollectionFilterFactory::withoutDeprecatedAndSameName($properties));

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

        $name           = $property->name();
        $camelCasedName = StringUtils::capitalizeWord($property->name());
        $annotatedType  = $property->typeAnnotation();

        $tags = [new ReturnTag($annotatedType)];
        if (PropertyQuery::isDeprecated($property)) {
            $tags[] = new GenericTag("deprecated");
        }

        $docBlockGenerator = new DocBlockGenerator(null, null, $tags);
        $docBlockGenerator->setWordWrap(false);  // needs to be disabled because its fundamentally broken

        $getMethod = new MethodGenerator(
            name: 'get' . $camelCasedName,
            parameters: [],
            flags: MethodGenerator::FLAG_PUBLIC,
            body: "return \$this->$name;",
            docBlock: $docBlockGenerator,
        );

        if ($this->generatorRequest->isAtLeastPHP("7.0")) {
            $typeHint = $property->typeHint($this->generatorRequest->getTargetPHPVersion());
            if ($typeHint !== null) {
                $getMethod->setReturnType($typeHint);

                if ($typeHint[0] === '?') {
                    $getMethod->setBody("return \$this->{$name} ?? null;");
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
        $methods    = [];
        $properties = $properties->filter(PropertyCollectionFilterFactory::withoutDeprecatedAndSameName($properties));

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
        $key           = $property->key();
        $name          = $property->name();
        $camelCaseName = StringUtils::capitalizeWord($name);

        $requiredProperty = ($property instanceof OptionalPropertyDecorator) ? $property->unwrap() : $property;

        $annotatedType = $requiredProperty->typeAnnotation();
        $typeHint      = $requiredProperty->typeHint($this->generatorRequest->getTargetPHPVersion());

        if ($property->isComplex()) {
            $setterValidation = "";
        } else {
            $setterValidation = "\$validator = new \JsonSchema\Validator();
\$validator->validate(\$$name, self::\$schema['properties']['$key']);
if (!\$validator->isValid()) {
    throw new \InvalidArgumentException(\$validator->getErrors()[0]['message']);
}

";
        }

        $tags = [
            new ParamTag($name, [str_replace("|null", "", $annotatedType)]),
            new ReturnTag("self"),
        ];

        if (PropertyQuery::isDeprecated($property)) {
            $tags[] = new GenericTag("deprecated");
        }

        $docBlock = new DocBlockGenerator(null, null, $tags);
        $docBlock->setWordWrap(false);

        $setMethod = new MethodGenerator(
            'with' . $camelCaseName,
            [new ParameterGenerator($name, $typeHint)],
            MethodGenerator::FLAG_PUBLIC,
            $setterValidation . "\$clone = clone \$this;
\$clone->$name = \$$name;

return \$clone;",
            $docBlock
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
        $name           = $property->name();
        $camelCasedName = StringUtils::capitalizeWord($name);

        $body = "\$clone = clone \$this;\n";
        if (isset($property->schema()["default"])) {
            $value = $property->formatValue($property->schema()["default"])->generate();
            $body .= "\$clone->$name = " . $value . "\n";
        } else {
            $body .= "unset(\$clone->$name);\n";
        }

        $body .= "\nreturn \$clone;";

        $unsetMethod = new MethodGenerator(
            'without' . $camelCasedName,
            [],
            MethodGenerator::FLAG_PUBLIC,
            $body,
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
        $params      = [];
        $tags        = [];
        $assignments = [];

        $requiredProperties = $properties->filter(PropertyCollectionFilterFactory::required());

        foreach ($requiredProperties as $requiredProperty) {
            $paramName = $requiredProperty->name();
            $params[]  = new ParameterGenerator(
                $paramName,
                $requiredProperty->typeHint($this->generatorRequest->getTargetPHPVersion())
            );

            $tags[] = new ParamTag(
                $paramName,
                [$requiredProperty->typeAnnotation()]
            );

            $assignments[] = "\$this->{$requiredProperty->name()} = \${$paramName};";
        }

        $docBlock = new DocBlockGenerator("", "", $tags);
        $docBlock->setWordWrap(false);

        return new MethodGenerator(
            "__construct",
            $params,
            MethodGenerator::FLAG_PUBLIC,
            join("\n", $assignments),
            $docBlock
        );
    }
}
