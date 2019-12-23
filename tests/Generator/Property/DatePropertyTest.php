<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use Helmich\Schema2Class\Spec\SpecificationOptions;
use Helmich\Schema2Class\Spec\ValidatedSpecificationFilesItem;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class DatePropertyTest extends TestCase
{

    private DateProperty $property;

    private GeneratorRequest $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = new GeneratorRequest([], new ValidatedSpecificationFilesItem("", "Foo", ""), new SpecificationOptions());
        $this->property = new DateProperty('myPropertyName', ['type' => 'string', 'format' => 'date-time'], $this->generatorRequest);
    }

    public function testCanHandleSchema()
    {
        assertTrue(DateProperty::canHandleSchema(['type' => 'string', 'format' => 'date-time']));

        assertFalse(DateProperty::canHandleSchema(['type' => 'string']));
        assertFalse(DateProperty::canHandleSchema(['type' => 'string', 'format' => 'foo']));

    }

    public function testIsComplex()
    {
        assertTrue($this->property->isComplex());
    }

    public function testConvertJsonToType()
    {
        $result = $this->property->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = new \DateTime($variable['myPropertyName']);
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJson()
    {
        $result = $this->property->convertTypeToJSON('variable');

        $expected = <<<'EOCODE'
$variable['myPropertyName'] = ($this->myPropertyName)->format(\DateTime::ATOM);
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
        assertSame('\\DateTime', $this->property->typeAnnotation());
        assertSame('\\DateTime', $this->property->typeHint("7.2.0"));
        assertSame('\\DateTime', $this->property->typeHint("5.6.0"));
    }

    public function testGenerateSubTypesWithSimpleArray()
    {
        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $this->property->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass(Argument::any(), Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }

}
