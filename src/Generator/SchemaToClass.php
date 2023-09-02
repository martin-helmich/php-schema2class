<?php
declare(strict_types=1);

namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Codegen\PropertyGenerator;
use Helmich\Schema2Class\Generator\Property\NestedObjectProperty;
use Helmich\Schema2Class\Generator\Property\PropertyCollection;
use Helmich\Schema2Class\Writer\WriterInterface;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\EnumGenerator\Cases\BackedCases;
use Laminas\Code\Generator\EnumGenerator\EnumGenerator;
use Laminas\Code\Generator\EnumGenerator\Name as EnumName;
use Symfony\Component\Console\Output\OutputInterface;
use Laminas\Code\DeclareStatement;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlock\Tag\GenericTag;
use Laminas\Code\Generator\FileGenerator;

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
     * @param GeneratorRequest $req
     * @throws GeneratorException
     */
    public function schemaToClass(GeneratorRequest $req): void
    {
        $schema = $req->getSchema();

        if (isset($schema["enum"])) {
            $this->schemaToEnum($req);
            return;
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
        $methods    = [];

        if (!NestedObjectProperty::canHandleSchema($schema)) {
            throw new GeneratorException("cannot generate class for types other than 'object'");
        }

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

        $methods[] = $codeGenerator->generateConstructor($propertiesFromSchema);

        $properties = array_merge($properties, $codeGenerator->generateProperties($propertiesFromSchema));
        $methods    = array_merge($methods, $codeGenerator->generateGetterMethods($propertiesFromSchema));
        $methods    = array_merge($methods, $codeGenerator->generateSetterMethods($propertiesFromSchema));

        $methods[] = $codeGenerator->generateBuildMethod($propertiesFromSchema);
        $methods[] = $codeGenerator->generateToJSONMethod($propertiesFromSchema);
        $methods[] = $codeGenerator->generateValidateMethod();
        $methods[] = $codeGenerator->generateCloneMethod($propertiesFromSchema);

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

        $file = new FileGenerator();
        $file->setClasses([$cls]);

        if ($req->isAtLeastPHP("7.0") && !$req->getOptions()->getDisableStrictTypes()) {
            $file->setDeclares([DeclareStatement::strictTypes(1)]);
        }

        $content = $file->generate();

        // Do some corrections because the Zend code generation library is stupid.
        $content = preg_replace('/ : \\\\self/', ' : self', $content);
        $content = preg_replace('/\\\\' . preg_quote($req->getTargetNamespace()) . '\\\\/', '', $content);

        $this->writer->writeFile($req->getTargetDirectory() . '/' . $req->getTargetClass() . '.php', $content);
    }

    private function schemaToEnum(GeneratorRequest $req): void
    {
        if (!$req->isAtLeastPHP("8.1")) {
            throw new GeneratorException("cannot generate enum classes for PHP versions < 8.1");
        }

        $cases = [];
        foreach ($req->getSchema()["enum"] as $case) {
            $cases[$case] = $case;
        }

        $type = $req->getSchema()["type"] === "string" ? "string" : "int";
        $enum = EnumGenerator::withConfig([
            "name"        => $req->getTargetNamespace() . "\\" . $req->getTargetClass(),
            "backedCases" => [
                "type"  => $type,
                "cases" => $cases,
            ],
        ]);

        $file = new FileGenerator();
        $file->setBody($enum->generate());

        // No strict typings for enums, because Psalm shits itself in that case.
        // $file->setDeclares([DeclareStatement::strictTypes(1)]);

        $content = $file->generate();

        // Do some corrections because the Zend code generation library is stupid.
        $content = preg_replace('/ : \\\\self/', ' : self', $content);
        $content = preg_replace('/\\\\' . preg_quote($req->getTargetNamespace()) . '\\\\/', '', $content);

        $this->writer->writeFile($req->getTargetDirectory() . '/' . $req->getTargetClass() . '.php', $content);
    }

}
