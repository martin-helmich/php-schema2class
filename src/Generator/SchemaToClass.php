<?php
declare(strict_types=1);

namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Generator\Property\IntersectProperty;
use Helmich\Schema2Class\Generator\Property\NestedObjectProperty;
use Helmich\Schema2Class\Generator\Property\PropertyCollection;
use Helmich\Schema2Class\Util\ErrorCorrection;
use Helmich\Schema2Class\Writer\WriterInterface;
use Laminas\Code\DeclareStatement;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlock\Tag\GenericTag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\EnumGenerator\EnumGenerator;
use Laminas\Code\Generator\FileGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\TypeGenerator;
use Symfony\Component\Console\Output\OutputInterface;

class SchemaToClass
{
    private WriterInterface $writer;
    private SchemaToEnum $enumGenerator;

    /**
     * @phpstan-ignore constructor.unusedParameter (kept for backwards compatibility)
     */
    public function __construct(WriterInterface $writer, OutputInterface $output)
    {
        $this->writer = $writer;
        $this->enumGenerator = new SchemaToEnum($writer);
    }

    /**
     * @param GeneratorRequest $req
     * @throws GeneratorException
     */
    public function schemaToClass(GeneratorRequest $req): void
    {
        $schema = $req->getSchema();

        if (isset($schema["enum"])) {
            $this->enumGenerator->schemaToEnum($req);
            return;
        }

        if (IntersectProperty::canHandleSchema($schema)) {
            $schema = (new IntersectProperty($req->getTargetClass(), $schema, $req))->buildSchemaIntersect();
        }

        if (!NestedObjectProperty::canHandleSchema($schema)) {
            throw new GeneratorException("cannot generate class for types other than 'object'");
        }

        $schemaProperty = new PropertyGenerator("internalValidationSchema", $schema, PropertyGenerator::FLAG_PRIVATE | PropertyGenerator::FLAG_STATIC);
        $schemaProperty->setDocBlock(new DocBlockGenerator(
            "Schema used to validate input for creating instances of this class",
            null,
            [new GenericTag("var", "array")]
        ));

        if ($req->isAtLeastPHP("7.4")) {
            $schemaProperty->setType(TypeGenerator::fromTypeString("array"));
        }

        $properties = [$schemaProperty];

        $propertiesFromSchema = new PropertyCollection();

        if (isset($schema["properties"])) {
            foreach ($schema["properties"] as $key => $definition) {
                $isRequired = isset($schema["required"]) && in_array($key, $schema["required"]);

                $property = PropertyBuilder::buildPropertyFromSchema($req, $key, $definition, $isRequired);
                $propertiesFromSchema->add($property);
            }
        }

        $additionalPropertiesItem = $this->buildAdditionalPropertiesItem($req, $schema);

        foreach ($propertiesFromSchema as $property) {
            $property->generateSubTypes($this);
        }

        $additionalPropertiesItem?->generateSubTypes($this);

        $codeGenerator = new Generator($req);

        $properties = [
            ...$properties,
            ...$codeGenerator->generateProperties($propertiesFromSchema),
        ];

        if ($additionalPropertiesItem !== null) {
            $properties[] = $codeGenerator->generateAdditionalPropertiesProperty($additionalPropertiesItem);
        }

        $methods = [
            $codeGenerator->generateConstructor($propertiesFromSchema),
            ...$codeGenerator->generateGetterMethods($propertiesFromSchema),
            ...$codeGenerator->generateSetterMethods($propertiesFromSchema),
            ...($additionalPropertiesItem !== null ? [
                $codeGenerator->generateAdditionalPropertiesGetter($additionalPropertiesItem),
                $codeGenerator->generateAdditionalPropertiesSetter($additionalPropertiesItem),
            ] : []),
            $codeGenerator->generateBuildMethod($propertiesFromSchema, $additionalPropertiesItem),
            $codeGenerator->generateToJSONMethod($propertiesFromSchema, $additionalPropertiesItem),
            $codeGenerator->generateValidateMethod(),
            $codeGenerator->generateCloneMethod($propertiesFromSchema, $additionalPropertiesItem),
        ];

        $cls = new ClassGenerator(
            $req->getTargetClass(),
            $req->getTargetNamespace(),
            null,
            null,
            [],
            $properties,
            $methods,
            null
        );

        $req->onClassCreated($cls);

        $filename = $req->getTargetDirectory() . '/' . $req->getTargetClass() . '.php';

        $file = new FileGenerator();
        $file->setClasses([$cls]);

        $req->onFileCreated($filename, $file);

        if ($req->isAtLeastPHP("7.0") && !$req->getOptions()->getDisableStrictTypes()) {
            $file->setDeclares([DeclareStatement::strictTypes(1)]);
        }

        // Do some corrections because the Zend code generation library is stupid.
        $correction = new ErrorCorrection($req->getTargetNamespace());
        $content = $file->generate()
            |> $correction->replaceIncorrectlyNamespacedSelf(...)
            |> $correction->replaceIncorrectFQCNs(...);

        $this->writer->writeFile($filename, $content);
    }

    /**
     * Builds a pseudo-property representing the value type of 'additionalProperties'
     * for schemas that combine 'properties' and 'additionalProperties'. Returns null
     * when the schema does not use this combination; purely map-like schemas (only
     * 'additionalProperties') are represented as plain array properties instead.
     *
     * @throws GeneratorException
     */
    private function buildAdditionalPropertiesItem(GeneratorRequest $req, array $schema): ?Property\PropertyInterface
    {
        $hasProperties           = isset($schema["properties"]) && count($schema["properties"]) > 0;
        $hasAdditionalProperties = isset($schema["additionalProperties"]) && is_array($schema["additionalProperties"]) && count($schema["additionalProperties"]) > 0;

        if (!$hasProperties || !$hasAdditionalProperties) {
            return null;
        }

        if (isset($schema["properties"]["additionalProperties"])) {
            throw new GeneratorException("schemas using 'additionalProperties' together with a regular property named 'additionalProperties' are not supported");
        }

        return PropertyBuilder::buildPropertyFromSchema($req, "additionalPropertiesItem", $schema["additionalProperties"], true);
    }

}
