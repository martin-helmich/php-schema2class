<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator;


use Helmich\Schema2Class\Writer\WriterInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class SchemaToClassTest extends \PHPUnit\Framework\TestCase
{

    /** @var SchemaToClass */
    private $underTest;

    protected function setUp(): void
    {
        $this->underTest = new SchemaToClass();
    }

    public function testSchemaToClass()
    {
        $generatorRequest = new GeneratorRequest(
            ['properties' => ['foo' => ['type' => 'string']]],
            __DIR__,
            'Ns',
            'Foo',
            '7.2',
        );
        $consoleOutput = $this->prophesize(ConsoleOutputInterface::class);
        $writer = $this->prophesize(WriterInterface::class);

        assertSame($this->underTest, $this->underTest->setOutput($consoleOutput->reveal()));
        assertSame($this->underTest, $this->underTest->setWriter($writer->reveal()));

        $this->underTest->schemaToClass($generatorRequest);
    }
}
