<?php

namespace Helmich\JsonStructBuilder\Generator;

use Helmich\JsonStructBuilder\Generator\Property\ArrayProperty;
use Helmich\JsonStructBuilder\Generator\Property\DateProperty;
use Helmich\JsonStructBuilder\Generator\Property\IntegerProperty;
use Helmich\JsonStructBuilder\Generator\Property\IntersectProperty;
use Helmich\JsonStructBuilder\Generator\Property\MixedProperty;
use Helmich\JsonStructBuilder\Generator\Property\NestedObjectProperty;
use Helmich\JsonStructBuilder\Generator\Property\OptionalPropertyDecorator;
use Helmich\JsonStructBuilder\Generator\Property\PropertyCollection;
use Helmich\JsonStructBuilder\Generator\Property\StringProperty;
use Helmich\JsonStructBuilder\Generator\Property\UnionProperty;
use Helmich\JsonStructBuilder\Writer\WriterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\VarTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\PropertyGenerator;

class SchemaToClass
{

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
        $methods = [];

        if (!isset($in->schema["properties"])) {
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

        $ctx = new GeneratorContext($in, $output, $writer);
        $gen = new Generator($ctx);

        foreach ($in->schema["properties"] as $key => $definition) {
            $isRequired = isset($in->schema["required"]) && in_array($key, $in->schema["required"]);

            foreach ($propertyTypes as $propertyType) {
                if ($propertyType::canHandleSchema($definition)) {
                    $output->writeln("building generator <info>$propertyType</info> for property <comment>$key</comment>");

                    $property = new $propertyType($key, $definition, $ctx);

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

        $methods[] = $gen->generateBuildMethod($propertiesFromSchema);
        $methods[] = $gen->generateToJSONMethod($propertiesFromSchema);
        $methods[] = $gen->generateValidateMethod($propertiesFromSchema);
        $methods[] = $gen->generateCloneMethod($propertiesFromSchema);

        $properties = array_merge($properties, $gen->generateProperties($propertiesFromSchema));
        $methods = array_merge($methods, $gen->generateGetterMethods($propertiesFromSchema));

        if (!$in->noSetters) {
            $methods = array_merge($methods, $gen->generateSetterMethods($propertiesFromSchema));
        }

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

}