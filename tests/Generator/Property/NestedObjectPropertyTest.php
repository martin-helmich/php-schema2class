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

    /** @var NestedObjectProperty */
    private $underTest;

    /** @var GeneratorRequest|\Prophecy\Prophecy\ObjectProphecy */
    private $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = new GeneratorRequest([], "", "BarNs", "Foo");
        $key = 'myPropertyName';
        $this->underTest = new NestedObjectProperty($key, ['allOf' => []], $this->generatorRequest);
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
        $underTest = new NestedObjectProperty('myPropertyName', ['allOf' => []], $this->generatorRequest);

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
        $underTest = new NestedObjectProperty('myPropertyName',  ['allOf' => []], $this->generatorRequest);

        assertSame('FooMyPropertyName', $underTest->typeAnnotation());
        assertSame('\\BarNs\\FooMyPropertyName', $underTest->typeHint(7));
        assertSame('\\BarNs\\FooMyPropertyName', $underTest->typeHint(5));
    }

    public function testGenerateSubTypesWithSimpleArray()
    {
        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $this->underTest->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass(Argument::that(function(GeneratorRequest $subReq) {
            return Assert::equalTo(['allOf' => []])->evaluate($subReq->getSchema());
        }))->shouldHaveBeenCalled();
    }

}
