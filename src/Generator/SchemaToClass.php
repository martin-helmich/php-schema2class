<?php
declare(strict_types=1);

namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Codegen\PropertyGenerator;
use Helmich\Schema2Class\Generator\Definitions\DefinitionsCollector;
use Helmich\Schema2Class\Generator\Definitions\DefinitionsGenerator;
use Helmich\Schema2Class\Generator\Definitions\DefinitionsReferenceLookup;
use Helmich\Schema2Class\Generator\Property\IntersectProperty;
use Helmich\Schema2Class\Generator\Property\NestedObjectProperty;
use Helmich\Schema2Class\Generator\Property\PropertyCollection;
use Helmich\Schema2Class\Writer\WriterInterface;
use Laminas\Code\DeclareStatement;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlock\Tag\GenericTag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\EnumGenerator\EnumGenerator;
use Laminas\Code\Generator\FileGenerator;
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

        $this->definitionsToSchemas($req);

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

        $schemaProperty = new PropertyGenerator("schema", $schema, PropertyGenerator::FLAG_PRIVATE | PropertyGenerator::FLAG_STATIC);
        $schemaProperty->setDocBlock(new DocBlockGenerator(
            "Schema used to validate input for creating instances of this class",
            null,
            [new GenericTag("var", "array")]
        ));

        if ($req->isAtLeastPHP("7.4")) {
            $schemaProperty->setTypeHint("array");
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

        foreach ($propertiesFromSchema as $property) {
            $property->generateSubTypes($this);
        }

        $codeGenerator = new Generator($req);

        $properties = [
            ...$properties,
            ...$codeGenerator->generateProperties($propertiesFromSchema),
        ];

        $methods = [
            $codeGenerator->generateConstructor($propertiesFromSchema),
            ...$codeGenerator->generateGetterMethods($propertiesFromSchema),
            ...$codeGenerator->generateSetterMethods($propertiesFromSchema),
            $codeGenerator->generateBuildMethod($propertiesFromSchema),
            $codeGenerator->generateToJSONMethod($propertiesFromSchema),
            $codeGenerator->generateValidateMethod(),
            $codeGenerator->generateCloneMethod($propertiesFromSchema),
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

        $content = $file->generate();

        // Do some corrections because the Zend code generation library is stupid.
        $content = preg_replace('/ : \\\\self/', ' : self', $content);
        $content = preg_replace('/\\\\' . preg_quote($req->getTargetNamespace(), '/') . '\\\\/', '', $content);

        $this->writer->writeFile($filename, $content);
    }

    private function definitionsToSchemas(GeneratorRequest &$req): void
    {
        if ($req->hasReferenceLookup(DefinitionsReferenceLookup::class)) {
            return;
        }

        $collector = new DefinitionsCollector($req);
        $collectedDefinitions = iterator_to_array($collector->collect($req->getSchema()));

        $req = $req->withAdditionalReferenceLookup(new DefinitionsReferenceLookup(
            $collectedDefinitions,
        ));

        $generator = new DefinitionsGenerator($this);
        $generator->generate($collectedDefinitions, $req);
    }

}
