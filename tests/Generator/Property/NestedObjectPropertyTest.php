<?php

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use PHPUnit\Framework\TestCase;

class NestedObjectPropertyTest extends TestCase
{

    /** @var NestedObjectProperty */
    private $underTest;

    /** @var GeneratorRequest|\Prophecy\Prophecy\ObjectProphecy */
    private $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = $this->prophesize(GeneratorRequest::class);
        $key = 'myPropertyName';
        $this->underTest = new NestedObjectProperty($key, ['allOf' => []], $this->generatorRequest->reveal());
    }

    public function testCanHandleSchema()
    {
        assertTrue(NestedObjectProperty::canHandleSchema(['type' => 'object']));
        assertTrue(NestedObjectProperty::canHandleSchema(['properties' => []]));
        assertFalse(NestedObjectProperty::canHandleSchema(['type' => 'foo']));
        assertFalse(NestedObjectProperty::canHandleSchema([]));
    }

    public function testIsComplex()
    {
        assertTrue($this->underTest->isComplex());
    }

    public function testConvertJsonToType()
    {
        $this->generatorRequest->getTargetClass()->willReturn('Foo');

        $underTest = new NestedObjectProperty('myPropertyName', ['allOf' => []], $this->generatorRequest->reveal());

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

        $underTest = new NestedObjectProperty('myPropertyName',  ['allOf' => []], $this->generatorRequest->reveal());

        assertSame('FooMyPropertyName', $underTest->typeAnnotation());
        assertSame('\\BarNs\\FooMyPropertyName', $underTest->typeHint(7));
        assertSame('\\BarNs\\FooMyPropertyName', $underTest->typeHint(5));
    }

    public function testGenerateSubTypesWithSimpleArray()
    {
        $this->generatorRequest->withSchema(['allOf' => []])->willReturn($this->generatorRequest->reveal());
        $this->generatorRequest->withClass('MyPropertyName')->willReturn($this->generatorRequest->reveal());
        $this->generatorRequest->getTargetClass()->willReturn('');

        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $this->underTest->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass($this->generatorRequest->reveal())->shouldHaveBeenCalled();
    }

}
