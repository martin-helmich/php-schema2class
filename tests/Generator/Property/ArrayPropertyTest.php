<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class ArrayPropertyTest extends TestCase
{

    private ArrayProperty $property;

    private GeneratorRequest $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = new GeneratorRequest([], "", "", "Foo");
        $this->property = new ArrayProperty('myPropertyName', ['type' => 'integer'], $this->generatorRequest);
    }

    public function testCanHandleSchema()
    {
        assertTrue(ArrayProperty::canHandleSchema(['type' => 'array']));
        assertFalse(ArrayProperty::canHandleSchema(['type' => 'foo']));
    }

    public function testConvertJsonToTypeWithSimpleArray()
    {
        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array'], $this->generatorRequest);

        assertFalse($underTest->isComplex());

        $result = $underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = $variable['myPropertyName'];
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertJsonToTypeWithComplexArray()
    {
        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $this->generatorRequest);

        assertTrue($underTest->isComplex());

        $result = $underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = array_map(function($i) { return FooMyPropertyNameItem::buildFromInput($i); }, $variable['myPropertyName']);
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJsonWithSimpleArray()
    {
        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array'], $this->generatorRequest);

        $result = $underTest->convertTypeToJSON('variable');

        $expected = <<<'EOCODE'
$variable['myPropertyName'] = $this->myPropertyName;
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJsonWithComplexArray()
    {
        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $this->generatorRequest);

        $result = $underTest->convertTypeToJSON('variable');

        $expected = <<<'EOCODE'
$variable['myPropertyName'] = array_map(function(FooMyPropertyNameItem $i) { return $i->toJson(); }, $this->myPropertyName);
EOCODE;

        assertSame($expected, $result);
    }

    public function testClonePropertyWithSimpleArray()
    {
        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array'], $this->generatorRequest);

        $expected = <<<'EOCODE'
$this->myPropertyName = clone $this->myPropertyName;
EOCODE;
        assertSame($expected, $underTest->cloneProperty());
    }

    public function testClonePropertyWithComplexArray()
    {
        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $this->generatorRequest);

        $expected = <<<'EOCODE'
$this->myPropertyName = array_map(function(FooMyPropertyNameItem $i) { return clone $i; }, $this->myPropertyName);
EOCODE;
        assertSame($expected, $underTest->cloneProperty());
    }

    public function testGetAnnotationAndHintWithSimpleArray()
    {
        assertSame('array', $this->property->typeAnnotation());
        assertSame('array', $this->property->typeHint("7.2.0"));
        assertSame('array', $this->property->typeHint("5.6.0"));
    }

    public function testGetAnnotationWithSimpleItemsArray()
    {
        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['type' => 'string']], $this->generatorRequest);

        assertSame('string[]', $underTest->typeAnnotation());
        assertSame('array', $underTest->typeHint("7.2.0"));
        assertSame('array', $underTest->typeHint("5.6.0"));

    }

    public function testGetAnnotationAndHintWithComplexArray()
    {
        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['properties' => []]], $this->generatorRequest);

        assertSame('FooMyPropertyNameItem[]', $underTest->typeAnnotation());
        assertSame('array', $underTest->typeHint("7.2.0"));
        assertSame('array', $underTest->typeHint("5.6.0"));

    }

    public function testGenerateSubTypesWithSimpleArray()
    {
        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $this->property->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass(Argument::any(), Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testGenerateSubTypesWithComplexArray()
    {
        $arrayProperties = ['properties' => []];
        $underTest = new ArrayProperty('myPropertyName', ['type' => 'array', 'items' => $arrayProperties], $this->generatorRequest);

        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $underTest->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass(Argument::that(function (GeneratorRequest $arg) use ($arrayProperties) {
            return $arg->getSchema() === $arrayProperties;
        }))->shouldHaveBeenCalled();
    }

}
