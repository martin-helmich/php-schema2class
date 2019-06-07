<?php

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorContext;
use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use Helmich\Schema2Class\Writer\WriterInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Output\OutputInterface;

class IntersectPropertyTest extends TestCase
{

    /** @var IntersectProperty */
    private $underTest;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $generatorContext;

    public function testCanHandleSchema()
    {
        assertTrue(IntersectProperty::canHandleSchema(['allOf' => []]));

        assertFalse(IntersectProperty::canHandleSchema([]));
    }

    protected function setUp()
    {
        $this->generatorContext = $this->prophesize(GeneratorContext::class);
        $key = 'myPropertyName';
        $this->underTest = new IntersectProperty($key, ['allOf' => []], $this->generatorContext->reveal());
    }

    public function testIsComplex()
    {
        assertTrue($this->underTest->isComplex());
    }

    public function testConvertJsonToType()
    {
        $ctx = $this->generatorContext->reveal();
        $ctx->request = new \stdClass();
        $ctx->request->targetClass = 'Foo';

        $underTest = new IntersectProperty('myPropertyName', ['allOf' => []], $ctx);

        $result = $underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = FooMyPropertyName::buildFromInput($variable['myPropertyName']);
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJson()
    {
        $result = $this->underTest->convertTypeToJSON('variable');

        $expected = <<<'EOCODE'
$variable['myPropertyName'] = $this->myPropertyName->toJson();
EOCODE;

        assertSame($expected, $result);
    }

    public function testCloneProperty()
    {
        $expected = <<<'EOCODE'
$this->myPropertyName = clone $this->myPropertyName;
EOCODE;
        assertSame($expected, $this->underTest->cloneProperty());
    }

    public function testGetAnnotationAndHintWithSimpleArray()
    {
        $ctx = $this->generatorContext->reveal();
        $ctx->request = new \stdClass();
        $ctx->request->targetClass = 'Foo';
        $ctx->request->targetNamespace = 'BarNs';

        $underTest = new IntersectProperty('myPropertyName', ['allOf' => []], $ctx);

        assertSame('FooMyPropertyName', $underTest->typeAnnotation());
        assertSame('\\BarNs\\FooMyPropertyName', $underTest->typeHint(7));
        assertSame('\\BarNs\\FooMyPropertyName', $underTest->typeHint(5));
    }


    public function provideTestSchema()
    {
        return [
            'empty allOf' => [
                ['allOf' => []],
                ['required' => [], 'properties' => []]
            ],
            'required' => [
                ['allOf' => [['required' => ['foo']], ['required' => ['bar']]]],
                ['required' => ['foo', 'bar'], 'properties' => []]
            ],
            'properties' => [
                ['allOf' => [
                    ['properties' => ['foo' => ['type' => 'int']]],
                    ['properties' => ['bar' => ['type' => 'date-time']]]
                ]],
                ['required' => [], 'properties' => ['foo' => ['type' => 'int'], 'bar' => ['type' => 'date-time']]]
            ],
            'oneOf inside' => [
                ['allOf' => [
                    ['oneOf' => [
                        ['required' => ['foo'], 'properties' => ['foo' => ['type' => 'int']]],
                        ['required' => ['bar', 'foo'], 'properties' => ['bar' => ['type' => 'date-time'], 'foo' => ['type' => 'string']]]
                    ]]
                ]],
                ['required' => ['foo'], 'properties' => ['bar' => ['type' => 'date-time'], 'foo' => ['type' => 'string']]]
            ],
            'anyOf inside' => [
                ['allOf' => [
                    ['anyOf' => [
                        ['required' => ['foo'], 'properties' => ['foo' => ['type' => 'int']]],
                        ['required' => ['bar'], 'properties' => ['bar' => ['type' => 'date-time']]]
                    ]]
                ]],
                ['required' => [], 'properties' => ['bar' => ['type' => 'date-time'], 'foo' => ['type' => 'int']]]
            ],
        ];
    }

    /**
     * @dataProvider provideTestSchema
     */
    public function testGenerateSubTypes($schema, $subschema)
    {
        $generatorRequest = $this->prophesize(GeneratorRequest::class);
        $generatorRequest->withSchema($subschema)->shouldBeCalled()->willReturn($generatorRequest->reveal());
        $generatorRequest->withClass('MyPropertyName')->shouldBeCalled()->willReturn($generatorRequest->reveal());

        $consoleOutput = $this->prophesize(OutputInterface::class);
        $writer = $this->prophesize(WriterInterface::class);

        $ctx = $this->generatorContext->reveal();
        $ctx->request = $generatorRequest->reveal();
        $ctx->output = $consoleOutput->reveal();
        $ctx->writer = $writer->reveal();

        $underTest = new IntersectProperty('myPropertyName', $schema, $ctx);

        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $underTest->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass($generatorRequest->reveal(), $consoleOutput->reveal(), $writer->reveal())->shouldHaveBeenCalled();
    }
}
