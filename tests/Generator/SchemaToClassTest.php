<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Spec\SpecificationOptions;
use Helmich\Schema2Class\Spec\ValidatedSpecificationFilesItem;
use Helmich\Schema2Class\Writer\DebugWriter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Yaml\Yaml;
use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\equalTo;

class SchemaToClassTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function loadCodeGenerationTestCases(): array
    {
        $testCases = [];
        $testCaseDir = join(DIRECTORY_SEPARATOR, [__DIR__, "Fixtures"]);

        $dir = opendir($testCaseDir);

        while ($entry = readdir($dir)) {
            if ($entry[0] === ".") {
                continue;
            }

            $schemaFile = join(DIRECTORY_SEPARATOR, [$testCaseDir, $entry, "schema.yaml"]);
            $outputDir = join(DIRECTORY_SEPARATOR, [$testCaseDir, $entry, "Output"]);
            $output = opendir($outputDir);

            $expectedFiles = [];
            $schema = Yaml::parseFile($schemaFile);

            while ($outputEntry = readdir($output)) {
                if (substr($outputEntry, -4) !== ".php") {
                    continue;
                }

                $expectedFiles[$outputEntry] = trim(file_get_contents(join(DIRECTORY_SEPARATOR, [$outputDir, $outputEntry])));
            }

            $testCases[$entry] = [$schema, $expectedFiles];
        }

        return $testCases;
    }

    /**
     * @param array $schema
     * @param array $expectedOutput
     * @dataProvider loadCodeGenerationTestCases
     */
    public function testCodeGeneration(array $schema, array $expectedOutput): void
    {
        $req = new GeneratorRequest(
            $schema,
            new ValidatedSpecificationFilesItem("Ns", "Foo", __DIR__),
            (new SpecificationOptions())->withTargetPHPVersion("7.4"),
        );

        $output = new NullOutput();
        $writer = new DebugWriter($output);

        (new SchemaToClassFactory())->build($writer, $output)->schemaToClass($req);

        foreach ($expectedOutput as $file => $content) {
            $filename = join(DIRECTORY_SEPARATOR, [__DIR__, $file]);
            assertThat($writer->getWrittenFiles()[$filename], equalTo($content));
        }
    }
}
