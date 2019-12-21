<?php

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class StringPropertyTest extends TestCase
{

    /** @var StringProperty */
    private $underTest;

    /** @var GeneratorRequest|\Prophecy\Prophecy\ObjectProphecy */
    private $generatorRequest;

    public function testCanHandleSchema()
    {
        $stringSchema = ['type' => 'string'];
        assertTrue(StringProperty::canHandleSchema($stringSchema));
        assertFalse(StringProperty::canHandleSchema([]));
    }

    protected function setUp()
    {
        $this->generatorRequest = $this->prophesize(GeneratorRequest::class);
        $key = 'myString';
        $this->underTest = new StringProperty($key, ['type' => 'string'], $this->generatorRequest->reveal());
    }

    public function testIsComplex()
    {
        assertFalse($this->underTest->isComplex());
    }

    public function testConvertJsonToType()
    {
        $result = $this->underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myString = $variable['myString'];
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJson()
    {
        $result = $this->underTest->convertTypeToJSON('variable');

        $expected = <<<'EOCODE'
$variable['myString'] = $this->myString;
EOCODE;

        assertSame($expected, $result);
    }

    public function testCloneProperty()
    {
        assertNull($this->underTest->cloneProperty());
    }

    public function testGetAnnotationAndHint()
    {
        assertSame('string', $this->underTest->typeAnnotation());
        assertSame('string', $this->underTest->typeHint(7));
        assertSame(null, $this->underTest->typeHint(5));
    }

    public function testGenerateSubTypesWithSimpleArray()
    {
        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $this->underTest->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass(Argument::any(), Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }

}
