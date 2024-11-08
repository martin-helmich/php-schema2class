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

        $this->definitionsToSchemas($req);

        if (isset($schema["enum"])) {
            $this->schemaToEnum($req);
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
        $content = preg_replace('/\\\\' . preg_quote($req->getTargetNamespace()) . '\\\\/', '', $content);

        $this->writer->writeFile($filename, $content);
    }

    /**
     * @param string|int $value
     * @return non-empty-string
     */
    private static function enumCaseName(string|int $value): string
    {
        if (is_int($value)) {
            return "VALUE_$value";
        }

        $value = static::enumCaseNameString($value);

        if (is_numeric($value[0])) {
            return "VALUE_$value";
        }

        if ($value === "") {
            return "EMPTY";
        }

        return $value;
    }

    /**
     * @param string $value
     * @return string
     */
    private static function enumCaseNameString(string $value): string
    {
        return preg_replace('/[^a-zA-Z0-9]/', '', $value);
    }

    /**
     * @param array<non-empty-string, string|int> $cases
     * @return array<non-empty-string, string|int>
     */
    private static function makeCaseNamesConsistent(array $cases): array
    {
        $hasValuePrefix = false;

        foreach ($cases as $name => $value) {
            if (str_starts_with($name, "VALUE_")) {
                $hasValuePrefix = true;
                break;
            }
        }

        if (!$hasValuePrefix) {
            return $cases;
        }

        $newCases = [];
        foreach ($cases as $name => $value) {
            if (str_starts_with($name, "VALUE_")) {
                $newCases[$name] = $value;
            } else {
                $newCases["VALUE_$name"] = $value;
            }
        }

        return $newCases;
    }

    private function schemaToEnum(GeneratorRequest $req): void
    {
        if (!$req->isAtLeastPHP("8.1")) {
            throw new GeneratorException("cannot generate enum classes for PHP versions < 8.1");
        }

        /** @var array<non-empty-string, string|int> $cases */
        $cases = [];
        foreach ($req->getSchema()["enum"] as $case) {
            if (!is_string($case) && !is_int($case)) {
                throw new GeneratorException("cannot generate enum classes for non-string/non-int enum values");
            }

            $name  = self::enumCaseName($case);
            $value = $case;

            $cases[$name] = $value;
        }

        $cases = self::makeCaseNamesConsistent($cases);

        $type     = $req->getSchema()["type"] === "string" ? "string" : "int";
        $enumName = $req->getTargetNamespace() . "\\" . $req->getTargetClass();
        $enum     = EnumGenerator::withConfig([
            "name"        => $enumName,
            "backedCases" => [
                "type"  => $type,
                "cases" => $cases,
            ],
        ]);

        $req->onEnumCreated($enumName, $enum);

        $filename = $req->getTargetDirectory() . '/' . $req->getTargetClass() . '.php';
        $file     = new FileGenerator();
        $file->setBody($enum->generate());

        $req->onFileCreated($filename, $file);

        // No strict typings for enums, because Psalm shits itself in that case.
        // $file->setDeclares([DeclareStatement::strictTypes(1)]);

        $content = $file->generate();

        // Do some corrections because the Zend code generation library is stupid.
        $content = preg_replace('/ : \\\\self/', ' : self', $content);
        $content = preg_replace('/\\\\' . preg_quote($req->getTargetNamespace()) . '\\\\/', '', $content);

        $this->writer->writeFile($filename, $content);
    }

    private function definitionsToSchemas(GeneratorRequest $req): void
    {
        if ($req->hasReferenceLookup(DefinitionsReferenceLookup::class)) {
            return;
        }

        $collector = new DefinitionsCollector();
        $collectedDefinitions = iterator_to_array($collector->collect($req->getSchema()));

        $req = $req->withReferenceLookup(new DefinitionsReferenceLookup(
            $collectedDefinitions,
        ));

        $generator = new DefinitionsGenerator($this);
        $generator->generate($collectedDefinitions, $req);
    }

}
