<?php

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class ArrayPropertyTest extends TestCase
{

    /** @var ArrayProperty */
    private $underTest;

    /** @var GeneratorRequest|\Prophecy\Prophecy\ObjectProphecy */
    private $generatorRequest;

    public function testCanHandleSchema()
    {
        assertTrue(ArrayProperty::canHandleSchema(['type' => 'array']));
        assertFalse(ArrayProperty::canHandleSchema(['type' => 'foo']));
    }

    protected function setUp()
    {
        $this->generatorRequest = $this->prophesize(GeneratorRequest::class);
        $key = 'myPropertyName';
        $this->underTest = new ArrayProperty($key, ['type' => 'integer'], $this->generatorRequest->reveal());
    }

    public function testConvertJsonToTypeWithSimpleArray()
    {
        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array'], $this->generatorRequest->reveal());

        assertFalse($underTest->isComplex());

        $result = $underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = $variable['myPropertyName'];
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertJsonToTypeWithComplexArray()
    {
        $this->generatorRequest->getTargetClass()->willReturn('Foo');

        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $this->generatorRequest->reveal());

        assertTrue($underTest->isComplex());

        $result = $underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = array_map(function($i) { return FooMyPropertyNameItem::buildFromInput($i); }, $variable['myPropertyName']);
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJsonWithSimpleArray()
    {
        $ctx = $this->generatorRequest->reveal();
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
        $this->generatorRequest->getTargetClass()->willReturn('Foo');

        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $this->generatorRequest->reveal());

        $result = $underTest->convertTypeToJSON('variable');

        $expected = <<<'EOCODE'
$variable['myPropertyName'] = array_map(function(FooMyPropertyNameItem $i) { return $i->toJson(); }, $this->myPropertyName);
EOCODE;

        assertSame($expected, $result);
    }

    public function testClonePropertyWithSimpleArray()
    {
        $ctx = $this->generatorRequest->reveal();
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
        $this->generatorRequest->getTargetClass()->willReturn('Foo');

        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $this->generatorRequest->reveal());

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
        $ctx = $this->generatorRequest->reveal();
        $ctx->request = new \stdClass();
        $ctx->request->targetClass = 'Foo';

        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['type' => 'string']], $ctx);

        assertSame('string[]', $underTest->typeAnnotation());
        assertSame('array', $underTest->typeHint(7));
        assertSame('array', $underTest->typeHint(5));

    }

    public function testGetAnnotationAndHintWithComplexArray()
    {
        $this->generatorRequest->getTargetClass()->willReturn('Foo');

        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $this->generatorRequest->reveal());

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
        $arrayProperties = ['properties' => []];
        $this->generatorRequest->withSchema($arrayProperties)->willReturn($this->generatorRequest->reveal());
        $this->generatorRequest->withClass('MyPropertyNameItem')->willReturn($this->generatorRequest->reveal());
        $this->generatorRequest->getTargetClass()->willReturn('');

        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => $arrayProperties], $this->generatorRequest->reveal());

        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $underTest->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass($this->generatorRequest->reveal())->shouldHaveBeenCalled();
        $this->generatorRequest->withSchema($arrayProperties)->shouldHaveBeenCalled();
        $this->generatorRequest->withClass('MyPropertyNameItem')->shouldHaveBeenCalled();
    }

}
