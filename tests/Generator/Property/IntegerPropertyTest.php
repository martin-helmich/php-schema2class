<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator\Property;


use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class IntegerPropertyTest extends TestCase
{

    private IntegerProperty $property;

    private GeneratorRequest $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = new GeneratorRequest([], "", "", "Foo");
        $this->property = new IntegerProperty('myPropertyName', ['type' => 'integer'], $this->generatorRequest);
    }

    public function testCanHandleSchema()
    {
        assertTrue(IntegerProperty::canHandleSchema(['type' => 'int']));
        assertTrue(IntegerProperty::canHandleSchema(['type' => 'integer']));
        assertTrue(IntegerProperty::canHandleSchema(['type' => 'number', 'format' => 'int']));
        assertTrue(IntegerProperty::canHandleSchema(['type' => 'number', 'format' => 'integer']));

        assertFalse(IntegerProperty::canHandleSchema([]));
        assertFalse(IntegerProperty::canHandleSchema(['type' => 'foo']));
        assertFalse(IntegerProperty::canHandleSchema(['type' => 'number', 'format' => 'foo']));
    }

    public function testIsComplex()
    {
        assertFalse($this->property->isComplex());
    }

    public function testConvertJsonToType()
    {
        $result = $this->property->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = (int) $variable['myPropertyName'];
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJson()
    {
        $result = $this->property->convertTypeToJSON('variable');

        $expected = <<<'EOCODE'
$variable['myPropertyName'] = $this->myPropertyName;
EOCODE;

        assertSame($expected, $result);
    }

    public function testCloneProperty()
    {
        assertNull($this->property->cloneProperty());
    }

    public function testGetAnnotationAndHintWithSimpleArray()
    {
        assertSame('int', $this->property->typeAnnotation());
        assertSame('int', $this->property->typeHint("7.2.0"));
        assertSame(null, $this->property->typeHint("5.6.0"));
    }

    public function testGenerateSubTypesWithSimpleArray()
    {
        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $this->property->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass(Argument::any(), Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }

}
