<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Codegen\PropertyGenerator;
use Helmich\Schema2Class\Generator\Property\ArrayProperty;
use Helmich\Schema2Class\Generator\Property\DateProperty;
use Helmich\Schema2Class\Generator\Property\IntegerProperty;
use Helmich\Schema2Class\Generator\Property\IntersectProperty;
use Helmich\Schema2Class\Generator\Property\MixedProperty;
use Helmich\Schema2Class\Generator\Property\NestedObjectProperty;
use Helmich\Schema2Class\Generator\Property\OptionalPropertyDecorator;
use Helmich\Schema2Class\Generator\Property\PropertyCollection;
use Helmich\Schema2Class\Generator\Property\StringProperty;
use Helmich\Schema2Class\Generator\Property\UnionProperty;
use Helmich\Schema2Class\Writer\WriterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\GenericTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\FileGenerator;

class SchemaToClass
{

    private WriterInterface $writer;

    private OutputInterface $output;

    /**
     * @param WriterInterface $writer
     * @return $this
     */
    public function setWriter(WriterInterface $writer): self
    {
        $this->writer = $writer;
        return $this;
    }

    /**
     * @param OutputInterface $output
     * @return $this
     */
    public function setOutput(OutputInterface $output): self
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @param GeneratorRequest $generatorRequest
     * @throws GeneratorException
     */
    public function schemaToClass(GeneratorRequest $generatorRequest): void
    {
        if (!$this->writer instanceof WriterInterface) {
            throw new UnexpectedValueException('A file writer has not been set.');
        }

        if (!$this->output instanceof OutputInterface) {
            throw new UnexpectedValueException('A console output has not been set.');
        }

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
        $propertyTypes = [
            IntersectProperty::class,
            UnionProperty::class,
            DateProperty::class,
            StringProperty::class,
            ArrayProperty::class,
            IntegerProperty::class,
            NestedObjectProperty::class,
            MixedProperty::class,
        ];

        foreach ($schema["properties"] as $key => $definition) {
            $isRequired = isset($schema["required"]) && in_array($key, $schema["required"]);

            foreach ($propertyTypes as $propertyType) {
                if ($propertyType::canHandleSchema($definition)) {
                    $this->output->writeln("building generator <info>$propertyType</info> for property <comment>$key</comment>");

                    $property = new $propertyType($key, $definition, $generatorRequest);

                    if (!$isRequired) {
                        $property = new OptionalPropertyDecorator($key, $property);
                    }

                    $propertiesFromSchema->add($property);

                    continue 2;
                }
            }
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

        $file = new FileGenerator([
            "classes" => [$cls],
        ]);

        $content = $file->generate();

        // Do some corrections because the Zend code generation library is stupid.
        $content = preg_replace('/ : \\\\self/', ' : self', $content);
        $content = preg_replace('/\\\\'.preg_quote($generatorRequest->getTargetNamespace()).'\\\\/', '', $content);

        $this->writer->writeFile($generatorRequest->getTargetDirectory() . '/' . $generatorRequest->getTargetClass() . '.php', $content);
    }

}
