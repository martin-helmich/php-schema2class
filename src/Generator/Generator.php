<?php

declare(strict_types=1);

namespace Helmich\Schema2Class\Generator;

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
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\PropertyValueGenerator;
use Laminas\Code\Generator\TypeGenerator;

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
                $prop->setType(TypeGenerator::fromTypeString($typeHint));
            }

            if (!$isOptional) {
                $prop->omitDefaultValue(true);
            }

            $propertyGenerators[] = $prop;
        }

        return $propertyGenerators;
    }

    /**
     * Generates the property holding all input properties that are not explicitly
     * declared in the schema (for schemas combining 'properties' and 'additionalProperties').
     */
    public function generateAdditionalPropertiesProperty(PropertyInterface $additionalPropertiesItem): PropertyGenerator
    {
        $prop = new PropertyGenerator(
            "additionalProperties",
            new PropertyValueGenerator([], PropertyValueGenerator::TYPE_ARRAY_SHORT, PropertyValueGenerator::OUTPUT_SINGLE_LINE),
            PropertyGenerator::FLAG_PRIVATE
        );

        $docBlock = new DocBlockGenerator(
            "Properties from the input that are not explicitly declared in the schema",
            null,
            [new GenericTag("var", "array<string, " . trim($additionalPropertiesItem->typeAnnotation()) . ">")]
        );
        $docBlock->setWordWrap(false);
        $prop->setDocBlock($docBlock);

        if ($this->generatorRequest->isAtLeastPHP("7.4")) {
            $prop->setType(TypeGenerator::fromTypeString("array"));
        }

        return $prop;
    }

    public function generateAdditionalPropertiesGetter(PropertyInterface $additionalPropertiesItem): MethodGenerator
    {
        $docBlock = new DocBlockGenerator(null, null, [
            new ReturnTag("array<string, " . trim($additionalPropertiesItem->typeAnnotation()) . ">"),
        ]);
        $docBlock->setWordWrap(false);

        $method = new MethodGenerator(
            'getAdditionalProperties',
            [],
            MethodGenerator::FLAG_PUBLIC,
            'return $this->additionalProperties;',
            $docBlock
        );

        if ($this->generatorRequest->isAtLeastPHP("7.0")) {
            $method->setReturnType("array");
        }

        return $method;
    }

    public function generateAdditionalPropertiesSetter(PropertyInterface $additionalPropertiesItem): MethodGenerator
    {
        $docBlock = new DocBlockGenerator(null, null, [
            new ParamTag("additionalProperties", ["array<string, " . trim($additionalPropertiesItem->typeAnnotation()) . ">"]),
            new ReturnTag("self"),
        ]);
        $docBlock->setWordWrap(false);

        $method = new MethodGenerator(
            'withAdditionalProperties',
            [new ParameterGenerator("additionalProperties", "array")],
            MethodGenerator::FLAG_PUBLIC,
            "\$clone = clone \$this;\n\$clone->additionalProperties = \$additionalProperties;\n\nreturn \$clone;",
            $docBlock
        );

        if ($this->generatorRequest->isAtLeastPHP("7.0")) {
            $method->setReturnType("self");
        }

        return $method;
    }

    /**
     * @param PropertyCollection $properties
     * @return MethodGenerator
     */
    public function generateBuildMethod(PropertyCollection $properties, ?PropertyInterface $additionalPropertiesItem = null): MethodGenerator
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
            $this->generateAdditionalPropertiesCollectionCode($properties, $additionalPropertiesItem, $inputVarName) .
            'return $obj;',
            $docBlock
        );

        if ($this->generatorRequest->isAtLeastPHP("7.0")) {
            $method->setReturnType($this->generatorRequest->getTargetNamespace() . "\\" . $this->generatorRequest->getTargetClass());
        }

        return $method;
    }

    private function generateAdditionalPropertiesCollectionCode(PropertyCollection $properties, ?PropertyInterface $additionalPropertiesItem, string $inputVarName): string
    {
        if ($additionalPropertiesItem === null) {
            return "";
        }

        $declaredKeys = [];
        foreach ($properties as $property) {
            $declaredKeys[] = var_export($property->key(), true);
        }

        $mapping = $additionalPropertiesItem->generateInputMappingExpr('$value');

        return "foreach (get_object_vars(\$$inputVarName) as \$key => \$value) {\n" .
            "    if (in_array(\$key, [" . join(", ", $declaredKeys) . "], true)) {\n" .
            "        continue;\n" .
            "    }\n" .
            "    \$obj->additionalProperties[\$key] = {$mapping};\n" .
            "}\n";
    }

    /**
     * @param PropertyCollection $properties
     * @return MethodGenerator
     */
    public function generateToJSONMethod(PropertyCollection $properties, ?PropertyInterface $additionalPropertiesItem = null): MethodGenerator
    {
        $docBlock = new DocBlockGenerator(
            "Converts this object back to a simple array that can be JSON-serialized",
            null,
            [new ReturnTag(["array"], "Converted array")]
        );
        $docBlock->setWordWrap(false);

        $additionalPropertiesCode = "";
        if ($additionalPropertiesItem !== null) {
            $mapping = $additionalPropertiesItem->generateOutputMappingExpr('$value');

            // Additional properties are written first so that declared properties
            // always win in case of a key collision.
            $additionalPropertiesCode =
                "foreach (\$this->additionalProperties as \$key => \$value) {\n" .
                "    \$output[\$key] = {$mapping};\n" .
                "}\n";
        }

        $method = new MethodGenerator(
            'toJson',
            [],
            MethodGenerator::FLAG_PUBLIC,
            '$output = [];' . "\n" .
            $additionalPropertiesCode .
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
            '$validator->validate($input, self::$internalValidationSchema);' . "\n\n" .
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
    public function generateCloneMethod(PropertyCollection $properties, ?PropertyInterface $additionalPropertiesItem = null): MethodGenerator
    {
        $clones = [];

        foreach ($properties as $property) {
            $c = $property->cloneProperty();
            if ($c !== null) {
                $clones[] = $c;
            }
        }

        if ($additionalPropertiesItem !== null) {
            $cloneExpr = $additionalPropertiesItem->generateCloneExpr('$value');
            if ($cloneExpr !== '$value') {
                $clones[] = $this->generatorRequest->isAtLeastPHP("7.4")
                    ? "\$this->additionalProperties = array_map(fn (\$value) => {$cloneExpr}, \$this->additionalProperties);"
                    : "\$this->additionalProperties = array_map(function (\$value) { return {$cloneExpr}; }, \$this->additionalProperties);";
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
\$validator->validate(\$$name, self::\$internalValidationSchema['properties']['$key']);
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

        $body      = $setterValidation . "\$clone = clone \$this;
\$clone->$name = \$$name;

return \$clone;";

        if ($this->generatorRequest->isAtLeastPHP("8.5")) {
            $body      = $setterValidation . "return clone(\$this, [
    '$name' => \$$name,    
]);
\$clone->$name = \$$name;";
        }

        $setMethod = new MethodGenerator(
            'with' . $camelCaseName,
            [new ParameterGenerator($name, $typeHint)],
            MethodGenerator::FLAG_PUBLIC,
            $body,
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
