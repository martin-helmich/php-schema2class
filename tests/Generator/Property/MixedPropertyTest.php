<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class MixedPropertyTest extends TestCase
{

    /** @var MixedProperty */
    private $underTest;

    /** @var GeneratorRequest */
    private $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = new GeneratorRequest([], "", "BarNs", "Foo");
        $key = 'myPropertyName';
        $this->underTest = new MixedProperty($key, [], $this->generatorRequest);
    }

    public function testCanHandleSchema()
    {
        assertTrue(MixedProperty::canHandleSchema([]));
    }

    public function testIsComplex()
    {
        assertFalse($this->underTest->isComplex());
    }

    public function testConvertJsonToType()
    {
        $result = $this->underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = $variable['myPropertyName'];
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
        assertSame('mixed', $this->underTest->typeAnnotation());
        assertSame(null, $this->underTest->typeHint(7));
        assertSame(null, $this->underTest->typeHint(5));
    }

    public function testGenerateSubTypesWithSimpleArray()
    {
        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $this->underTest->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass(Argument::any(), Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }


}
