<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class PrimitiveArrayPropertyTest extends TestCase
{
    private PrimitiveArrayProperty $property;

    private GeneratorRequest $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = new GeneratorRequest([], "", "", "Foo");
        $this->property = new PrimitiveArrayProperty('myPropertyName', ['type' => 'integer'], $this->generatorRequest);
    }

    public function testCanHandleSchema()
    {
        assertTrue(PrimitiveArrayProperty::canHandleSchema(['type' => 'array', 'items' => ['type' => 'string']]));
        assertFalse(PrimitiveArrayProperty::canHandleSchema(['type' => 'foo']));
    }

    public function testConvertJsonToTypeWithSimpleArray()
    {
        $underTest = new PrimitiveArrayProperty('myPropertyName', ['type' => 'array'], $this->generatorRequest);

        assertFalse($underTest->isComplex());

        $result = $underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = $variable['myPropertyName'];
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJsonWithSimpleArray()
    {
        $underTest = new PrimitiveArrayProperty('myPropertyName', ['type' => 'array'], $this->generatorRequest);

        $result = $underTest->convertTypeToJSON('variable');

        $expected = <<<'EOCODE'
$variable['myPropertyName'] = $this->myPropertyName;
EOCODE;

        assertSame($expected, $result);
    }

    public function testClonePropertyWithSimpleArray()
    {
        $underTest = new PrimitiveArrayProperty('myPropertyName', ['type' => 'array'], $this->generatorRequest);

        assertThat($underTest->cloneProperty(), isNull());
    }

    public function testGetAnnotationAndHintWithSimpleArray()
    {
        assertSame('array', $this->property->typeAnnotation());
        assertSame('array', $this->property->typeHint("7.2.0"));
        assertSame('array', $this->property->typeHint("5.6.0"));
    }

    public function testGetAnnotationWithSimpleItemsArray()
    {
        $underTest = new PrimitiveArrayProperty('myPropertyName', ['type' => 'array', 'items' => ['type' => 'string']], $this->generatorRequest);

        assertSame('string[]', $underTest->typeAnnotation());
        assertSame('array', $underTest->typeHint("7.2.0"));
        assertSame('array', $underTest->typeHint("5.6.0"));

    }

    public function testGenerateSubTypesWithSimpleArray()
    {
        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $this->property->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass(Argument::any(), Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }

}
