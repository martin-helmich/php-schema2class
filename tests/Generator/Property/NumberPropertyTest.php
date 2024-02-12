<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator\Property;


use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use Helmich\Schema2Class\Spec\SpecificationOptions;
use Helmich\Schema2Class\Spec\ValidatedSpecificationFilesItem;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

class NumberPropertyTest extends TestCase
{
    use ProphecyTrait;

    private NumberProperty $property;

    private GeneratorRequest $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = new GeneratorRequest(
            [],
            new ValidatedSpecificationFilesItem("", "Foo", ""),
            new SpecificationOptions(),
        );
        $this->property = new NumberProperty('myPropertyName', ['type' => 'Number'], $this->generatorRequest);
    }

    public function testCanHandleSchema()
    {
        assertTrue($this->property::canHandleSchema(['type' => 'number']));

        assertFalse($this->property::canHandleSchema([]));
		assertFalse($this->property::canHandleSchema(['type' => 'integer']));
		assertFalse($this->property::canHandleSchema(['type' => 'float']));
        assertFalse($this->property::canHandleSchema(['type' => 'foo']));
    }

    public function testIsComplex()
    {
        assertFalse($this->property->isComplex());
    }

    public function testConvertJsonToType()
    {
        $result = $this->property->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = str_contains((string)($variable['myPropertyName']), '.') ? (float)($variable['myPropertyName']) : (int)($variable['myPropertyName']);
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
        assertSame('int|float', $this->property->typeAnnotation());
        assertSame(null, $this->property->typeHint("7.2.0"));
        assertSame(null, $this->property->typeHint("5.6.0"));
    }

    public function testGenerateSubTypesWithSimpleArray()
    {
        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $this->property->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass(Argument::any(), Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }

}
