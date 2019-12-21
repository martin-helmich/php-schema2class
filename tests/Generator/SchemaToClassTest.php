<?php

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
        $generatorRequest = $this->prophesize(GeneratorRequest::class);
        $consoleOutput = $this->prophesize(ConsoleOutputInterface::class);
        $writer = $this->prophesize(WriterInterface::class);

        $generatorRequest->getSchema()->shouldBeCalled()->willReturn(['properties' => ['foo' => ['type' => 'string']]]);
        $generatorRequest->isPhp(7)->willReturn(true);
        $generatorRequest->isPhp(5)->willReturn(false);
        $generatorRequest->getPhpTargetVersion()->willReturn(7);
        $generatorRequest->getTargetClass()->willReturn('Foo');
        $generatorRequest->getTargetNamespace()->willReturn('Ns');
        $generatorRequest->getTargetDirectory()->willReturn(__DIR__);

        assertSame($this->underTest, $this->underTest->setOutput($consoleOutput->reveal()));
        assertSame($this->underTest, $this->underTest->setWriter($writer->reveal()));

        $this->underTest->schemaToClass($generatorRequest->reveal());
    }
}
