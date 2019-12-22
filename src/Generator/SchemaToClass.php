<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Codegen\PropertyGenerator;
use Helmich\Schema2Class\Generator\Property\PropertyCollection;
use Helmich\Schema2Class\Writer\WriterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Code\DeclareStatement;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\GenericTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\FileGenerator;

class SchemaToClass
{
    private WriterInterface $writer;
    private OutputInterface $output;

    public function __construct(WriterInterface $writer, OutputInterface $output)
    {
        $this->writer = $writer;
        $this->output = $output;
    }

    /**
     * @param GeneratorRequest $generatorRequest
     * @throws GeneratorException
     */
    public function schemaToClass(GeneratorRequest $generatorRequest): void
    {
        $schema = $generatorRequest->getSchema();
        $schemaProperty = new PropertyGenerator("schema", $schema, PropertyGenerator::FLAG_PRIVATE | PropertyGenerator::FLAG_STATIC);
        $schemaProperty->setDocBlock(new DocBlockGenerator(
            "Schema used to validate input for creating instances of this class",
            null,
            [new GenericTag("var", "array")]
        ));

        if ($generatorRequest->isAtLeastPHP("7.4")) {
            $schemaProperty->setTypeHint("array");
        }

        $properties = [$schemaProperty];
        $methods = [];

        if (!isset($schema["properties"])) {
            throw new GeneratorException("cannot generate class for types other than 'object'");
        }

        $propertiesFromSchema = new PropertyCollection();

        foreach ($schema["properties"] as $key => $definition) {
            $isRequired = isset($schema["required"]) && in_array($key, $schema["required"]);

            $property = PropertyBuilder::buildPropertyFromSchema($generatorRequest, $key, $definition, $isRequired);
            $propertiesFromSchema->add($property);
        }

        foreach ($propertiesFromSchema as $property) {
            $property->generateSubTypes($this);
        }

        $codeGenerator = new Generator($generatorRequest);

        $methods[] = $codeGenerator->generateConstructor($propertiesFromSchema);

        $properties = array_merge($properties, $codeGenerator->generateProperties($propertiesFromSchema));
        $methods = array_merge($methods, $codeGenerator->generateGetterMethods($propertiesFromSchema));
        $methods = array_merge($methods, $codeGenerator->generateSetterMethods($propertiesFromSchema));

        $methods[] = $codeGenerator->generateBuildMethod($propertiesFromSchema);
        $methods[] = $codeGenerator->generateToJSONMethod($propertiesFromSchema);
        $methods[] = $codeGenerator->generateValidateMethod($propertiesFromSchema);
        $methods[] = $codeGenerator->generateCloneMethod($propertiesFromSchema);

        $cls = new ClassGenerator(
            $generatorRequest->getTargetClass(),
            $generatorRequest->getTargetNamespace(),
            null,
            null,
            [],
            $properties,
            $methods,
            null
        );

        $file = new FileGenerator();
        $file->setClasses([$cls]);
        $file->setDeclares([DeclareStatement::strictTypes(1)]);

        $content = $file->generate();

        // Do some corrections because the Zend code generation library is stupid.
        $content = preg_replace('/ : \\\\self/', ' : self', $content);
        $content = preg_replace('/\\\\'.preg_quote($generatorRequest->getTargetNamespace()).'\\\\/', '', $content);

        $this->writer->writeFile($generatorRequest->getTargetDirectory() . '/' . $generatorRequest->getTargetClass() . '.php', $content);
    }

}
