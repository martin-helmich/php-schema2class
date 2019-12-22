<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class NestedObjectPropertyTest extends TestCase
{

    private NestedObjectProperty $property;

    private GeneratorRequest $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = new GeneratorRequest([], "", "BarNs", "Foo");
        $this->property = new NestedObjectProperty('myPropertyName', ['allOf' => []], $this->generatorRequest);
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
        assertTrue($this->property->isComplex());
    }

    public function testConvertJsonToType()
    {
        $underTest = new NestedObjectProperty('myPropertyName', ['allOf' => []], $this->generatorRequest);

        $result = $underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = FooMyPropertyName::buildFromInput($variable['myPropertyName']);
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJson()
    {
        $result = $this->property->convertTypeToJSON('variable');

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
        assertSame($expected, $this->property->cloneProperty());
    }

    public function testGetAnnotationAndHintWithSimpleArray()
    {
        $underTest = new NestedObjectProperty('myPropertyName',  ['allOf' => []], $this->generatorRequest);

        assertSame('FooMyPropertyName', $underTest->typeAnnotation());
        assertSame('\\BarNs\\FooMyPropertyName', $underTest->typeHint("7.2.0"));
        assertSame('\\BarNs\\FooMyPropertyName', $underTest->typeHint("5.6.0"));
    }

    public function testGenerateSubTypesWithSimpleArray()
    {
        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $this->property->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass(Argument::that(function(GeneratorRequest $subReq) {
            return Assert::equalTo(['allOf' => []])->evaluate($subReq->getSchema());
        }))->shouldHaveBeenCalled();
    }

}
