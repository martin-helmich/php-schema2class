<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator\Property;


use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class IntegerPropertyTest extends TestCase
{

    /** @var IntegerProperty */
    private $underTest;

    /** @var GeneratorRequest */
    private $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = new GeneratorRequest([], "", "", "Foo");
        $key = 'myPropertyName';
        $this->underTest = new IntegerProperty($key, ['type' => 'integer'], $this->generatorRequest);
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
        assertFalse($this->underTest->isComplex());
    }

    public function testConvertJsonToType()
    {
        $result = $this->underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = (int) $variable['myPropertyName'];
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJson()
    {
        $result = $this->underTest->convertTypeToJSON('variable');

        $expected = <<<'EOCODE'
$variable['myPropertyName'] = $this->myPropertyName;
EOCODE;

        assertSame($expected, $result);
    }

    public function testCloneProperty()
    {
        assertNull($this->underTest->cloneProperty());
    }

    public function testGetAnnotationAndHintWithSimpleArray()
    {
        assertSame('int', $this->underTest->typeAnnotation());
        assertSame('int', $this->underTest->typeHint(7));
        assertSame(null, $this->underTest->typeHint(5));
    }

    public function testGenerateSubTypesWithSimpleArray()
    {
        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $this->underTest->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass(Argument::any(), Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }

}
