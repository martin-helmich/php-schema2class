<?php

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorContext;
use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use Helmich\Schema2Class\Writer\WriterInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Output\OutputInterface;

class ArrayPropertyTest extends TestCase
{

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $generatorContext;

    /** @var ArrayProperty */
    private $underTest;

    public function testCanHandleSchema()
    {
        assertTrue(ArrayProperty::canHandleSchema(['type' => 'array']));
        assertFalse(ArrayProperty::canHandleSchema(['type' => 'foo']));
    }

    protected function setUp()
    {
        $this->generatorContext = $this->prophesize(GeneratorContext::class);
        $key = 'myPropertyName';
        $this->underTest = new ArrayProperty($key, ['type' => 'integer'], $this->generatorContext->reveal());
    }

    public function testConvertJsonToTypeWithSimpleArray()
    {
        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array'], $this->generatorContext->reveal());

        assertFalse($underTest->isComplex());

        $result = $underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = $variable['myPropertyName'];
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertJsonToTypeWithComplexArray()
    {
        $ctx = $this->generatorContext->reveal();
        $ctx->request = new \stdClass();
        $ctx->request->targetClass = 'Foo';

        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $ctx);

        assertTrue($underTest->isComplex());

        $result = $underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = array_map(function($i) { return FooMyPropertyNameItem::buildFromInput($i); }, $variable['myPropertyName']);
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJsonWithSimpleArray()
    {
        $ctx = $this->generatorContext->reveal();
        $ctx->request = new \stdClass();
        $ctx->request->targetClass = 'Foo';

        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array'], $ctx);

        $result = $underTest->convertTypeToJSON('variable');

        $expected = <<<'EOCODE'
$variable['myPropertyName'] = $this->myPropertyName;
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJsonWithComplexArray()
    {
        $ctx = $this->generatorContext->reveal();
        $ctx->request = new \stdClass();
        $ctx->request->targetClass = 'Foo';

        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $ctx);

        $result = $underTest->convertTypeToJSON('variable');

        $expected = <<<'EOCODE'
$variable['myPropertyName'] = array_map(function(FooMyPropertyNameItem $i) { return $i->toJson(); }, $this->myPropertyName);
EOCODE;

        assertSame($expected, $result);
    }

    public function testClonePropertyWithSimpleArray()
    {
        $ctx = $this->generatorContext->reveal();
        $ctx->request = new \stdClass();
        $ctx->request->targetClass = 'Foo';

        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array'], $ctx);

        $expected = <<<'EOCODE'
$this->myPropertyName = clone $this->myPropertyName;
EOCODE;
        assertSame($expected, $underTest->cloneProperty());
    }

    public function testClonePropertyWithComplexArray()
    {
        $ctx = $this->generatorContext->reveal();
        $ctx->request = new \stdClass();
        $ctx->request->targetClass = 'Foo';

        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $ctx);

        $expected = <<<'EOCODE'
$this->myPropertyName = array_map(function(FooMyPropertyNameItem $i) { return clone $i; }, $this->myPropertyName);
EOCODE;
        assertSame($expected, $underTest->cloneProperty());
    }

    public function testGetAnnotationAndHintWithSimpleArray()
    {
        assertSame('array', $this->underTest->typeAnnotation());
        assertSame('array', $this->underTest->typeHint(7));
        assertSame('array', $this->underTest->typeHint(5));
    }

    public function testGetAnnotationWithSimpleItemsArray()
    {
        $ctx = $this->generatorContext->reveal();
        $ctx->request = new \stdClass();
        $ctx->request->targetClass = 'Foo';

        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['type' => 'string']], $ctx);

        assertSame('string[]', $underTest->typeAnnotation());
        assertSame('array', $underTest->typeHint(7));
        assertSame('array', $underTest->typeHint(5));

    }

    public function testGetAnnotationAndHintWithComplexArray()
    {
        $ctx = $this->generatorContext->reveal();
        $ctx->request = new \stdClass();
        $ctx->request->targetClass = 'Foo';

        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $ctx);

        assertSame('FooMyPropertyNameItem[]', $underTest->typeAnnotation());
        assertSame('array', $underTest->typeHint(7));
        assertSame('array', $underTest->typeHint(5));

    }

    public function testGenerateSubTypesWithSimpleArray()
    {
        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $this->underTest->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass(Argument::any(), Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testGenerateSubTypesWithComplexArray()
    {
        $generatorRequest = $this->prophesize(GeneratorRequest::class);
        $generatorRequest->withSchema(['properties' => []])->shouldBeCalled()->willReturn($generatorRequest->reveal());
        $generatorRequest->withClass('MyPropertyNameItem')->shouldBeCalled()->willReturn($generatorRequest->reveal());

        $consoleOutput = $this->prophesize(OutputInterface::class);
        $writer = $this->prophesize(WriterInterface::class);

        $ctx = $this->generatorContext->reveal();
        $ctx->request = $generatorRequest->reveal();
        $ctx->output = $consoleOutput->reveal();
        $ctx->writer = $writer->reveal();


        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $ctx);

        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $underTest->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass($generatorRequest->reveal(), $consoleOutput->reveal(), $writer->reveal())->shouldHaveBeenCalled();
    }

}
