<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class StringPropertyTest extends TestCase
{

    private StringProperty $property;

    private GeneratorRequest $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = new GeneratorRequest([], "", "BarNs", "Foo");
        $this->property = new StringProperty('myString', ['type' => 'string'], $this->generatorRequest);
    }

    public function testCanHandleSchema()
    {
        $stringSchema = ['type' => 'string'];
        assertTrue(StringProperty::canHandleSchema($stringSchema));
        assertFalse(StringProperty::canHandleSchema([]));
    }

    public function testIsComplex()
    {
        assertFalse($this->property->isComplex());
    }

    public function testConvertJsonToType()
    {
        $result = $this->property->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myString = $variable['myString'];
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJson()
    {
        $result = $this->property->convertTypeToJSON('variable');

        $expected = <<<'EOCODE'
$variable['myString'] = $this->myString;
EOCODE;

        assertSame($expected, $result);
    }

    public function testCloneProperty()
    {
        assertNull($this->property->cloneProperty());
    }

    public function testGetAnnotationAndHint()
    {
        assertSame('string', $this->property->typeAnnotation());
        assertSame('string', $this->property->typeHint("7.2.0"));
        assertSame(null, $this->property->typeHint("5.6.0"));
    }

    public function testGenerateSubTypesWithSimpleArray()
    {
        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $this->property->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass(Argument::any(), Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }

}
