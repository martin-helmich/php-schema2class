<?php
declare(strict_types=1);

namespace Helmich\Schema2Class\Generator;

use FilesystemIterator;
use Helmich\Schema2Class\Example\CustomerAddress;
use Helmich\Schema2Class\Loader\SchemaLoader;
use Helmich\Schema2Class\Spec\SpecificationOptions;
use Helmich\Schema2Class\Spec\ValidatedSpecificationFilesItem;
use Helmich\Schema2Class\Writer\DebugWriter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Yaml\Yaml;
use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\equalTo;

class SchemaToClassTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public static function loadCodeGenerationTestCases(): array
    {
        $testCases   = [];
        $testCaseDir = join(DIRECTORY_SEPARATOR, [__DIR__, "Fixtures"]);

        $dir = opendir($testCaseDir);

        while ($entry = readdir($dir)) {
            if ($entry[0] === ".") {
                continue;
            }

            $schemaFile = join(DIRECTORY_SEPARATOR, [$testCaseDir, $entry, "schema.yaml"]);
            if (!file_exists($schemaFile)) {
                $schemaFile = join(DIRECTORY_SEPARATOR, [$testCaseDir, $entry, "schema.json"]);
            }

            $optionsFile = join(DIRECTORY_SEPARATOR, [$testCaseDir, $entry, "options.yaml"]);
            $outputDir  = join(DIRECTORY_SEPARATOR, [$testCaseDir, $entry, "Output"]);

            try {
                $outputDirectoryIterator = new RecursiveDirectoryIterator($outputDir, FilesystemIterator::SKIP_DOTS);
                $outputIterator = new RecursiveIteratorIterator($outputDirectoryIterator);
            } catch (\UnexpectedValueException) {
                throw new \Exception("Could not open output directory for test case '{$entry}'");
            }

            $expectedFiles = [];
            $schema        = (new SchemaLoader())->loadSchema($schemaFile);

            $opts = (new SpecificationOptions)
                ->withTargetPHPVersion("8.2")
                ->withInlineAllofReferences(true);
            if (file_exists($optionsFile)) {
                $optsYaml = Yaml::parseFile($optionsFile);
                $opts = SpecificationOptions::buildFromInput($optsYaml);
            }

            /** @var \SplFileInfo $fileInfo */
            foreach ($outputIterator as $fileInfo) {
                if ($fileInfo->getExtension() !== 'php') {
                    continue;
                }

                $outputEntry = $fileInfo->getBasename();

                /** @var RecursiveDirectoryIterator $directoryIterator */
                $directoryIterator = $outputIterator->getInnerIterator();
                if ($directoryIterator->getSubPath()) {
                    $outputEntry = join([$directoryIterator->getSubPath(), DIRECTORY_SEPARATOR, $outputEntry]);
                }
                $expectedFiles[$outputEntry] = trim(file_get_contents($fileInfo->getPathname()));
            }

            $testCases[$entry] = [$entry, $schema, $expectedFiles, $opts];
        }

        return $testCases;
    }

    #[DataProvider("loadCodeGenerationTestCases")]
    public function testCodeGeneration(string $name, array $schema, array $expectedOutput, SpecificationOptions $opts): void
    {
        $req = new GeneratorRequest(
            $schema,
            new ValidatedSpecificationFilesItem("Ns\\{$name}", "Foo", __DIR__),
            $opts,
        );

        $req = $req->withReferenceLookup(new class ($schema) implements ReferenceLookup {
            public function __construct(private readonly array $schema)
            {
            }

            public function lookupReference(string $reference): ReferencedType
            {
                if ($reference === "#/properties/address") {
                    return new ReferencedTypeClass(CustomerAddress::class);
                }
                return new ReferencedTypeUnknown();
            }

            public function lookupSchema(string $reference): array
            {
                if ($reference === "#/properties/address") {
                    return [
                        'required' => [
                            'city',
                            'street',
                        ],
                        'properties' => [
                            'city' => [
                                'type' => 'string',
                                'maxLength' => 32,
                            ],
                            'street' => [
                                'type' => 'string',
                            ],
                        ],
                    ];
                }
                return [];
            }
        });

        $output = new NullOutput();
        $writer = new DebugWriter($output);

        (new SchemaToClassFactory())->build($writer, $output)->schemaToClass($req);

        foreach ($expectedOutput as $file => $content) {
            $filename      = join(DIRECTORY_SEPARATOR, [__DIR__, $file]);
            $actualContent = $writer->getWrittenFiles()[$filename];

            if (getenv("UPDATE_SNAPSHOTS") === "1") {
                $outputFilename = join(DIRECTORY_SEPARATOR, [__DIR__, "Fixtures", $name, "Output", $file]);
                file_put_contents($outputFilename, $actualContent);
            } else {
                assertThat($actualContent, equalTo($content));
            }
        }
    }
}
