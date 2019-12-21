<?php

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use PHPUnit\Framework\TestCase;

class IntersectPropertyTest extends TestCase
{

    /** @var IntersectProperty */
    private $underTest;

    /** @var GeneratorRequest|\Prophecy\Prophecy\ObjectProphecy */
    private $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = $this->prophesize(GeneratorRequest::class);
        $key = 'myPropertyName';
        $this->underTest = new IntersectProperty($key, ['allOf' => []], $this->generatorRequest->reveal());
    }

    public function testCanHandleSchema()
    {
        assertTrue(IntersectProperty::canHandleSchema(['allOf' => []]));

        assertFalse(IntersectProperty::canHandleSchema([]));
    }

    public function testIsComplex()
    {
        assertTrue($this->underTest->isComplex());
    }

    public function testConvertJsonToType()
    {
        $this->generatorRequest->getTargetClass()->willReturn('Foo');

        $underTest = new IntersectProperty('myPropertyName', ['allOf' => []], $this->generatorRequest->reveal());

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
        $this->generatorRequest->getTargetClass()->willReturn('Foo');
        $this->generatorRequest->getTargetNamespace()->willReturn('BarNs');

        $underTest = new IntersectProperty('myPropertyName', ['allOf' => []], $this->generatorRequest->reveal());

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

        $this->generatorRequest->withSchema($subschema)->willReturn($this->generatorRequest->reveal());
        $this->generatorRequest->withClass('MyPropertyName')->willReturn($this->generatorRequest->reveal());
        $this->generatorRequest->getTargetClass()->willReturn('');

        $underTest = new IntersectProperty('myPropertyName', $schema, $this->generatorRequest->reveal());

        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $underTest->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass($this->generatorRequest->reveal())->shouldHaveBeenCalled();
    }
}
