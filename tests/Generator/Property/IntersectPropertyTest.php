<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use Helmich\Schema2Class\Spec\SpecificationOptions;
use Helmich\Schema2Class\Spec\ValidatedSpecificationFilesItem;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

class IntersectPropertyTest extends TestCase
{
    use ProphecyTrait;

    private IntersectProperty $property;

    private GeneratorRequest $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = new GeneratorRequest(
            [],
            new ValidatedSpecificationFilesItem("BarNs", "Foo", ""),
            new SpecificationOptions(),
        );
        $this->property = new IntersectProperty('myPropertyName', ['allOf' => []], $this->generatorRequest);
    }

    public function testCanHandleSchema()
    {
        assertTrue(IntersectProperty::canHandleSchema(['allOf' => []]));

        assertFalse(IntersectProperty::canHandleSchema([]));
    }

    public function testIsComplex()
    {
        assertTrue($this->property->isComplex());
    }

    public function testConvertJsonToType()
    {
        $underTest = new IntersectProperty('myPropertyName', ['allOf' => []], $this->generatorRequest);

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
$variable['myPropertyName'] = ($this->myPropertyName)->toJson();
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
        $underTest = new IntersectProperty('myPropertyName', ['allOf' => []], $this->generatorRequest);

        assertSame('FooMyPropertyName', $underTest->typeAnnotation());
        assertSame('\\BarNs\\FooMyPropertyName', $underTest->typeHint("7.2.0"));
        assertSame('\\BarNs\\FooMyPropertyName', $underTest->typeHint("5.6.0"));
    }


    public function provideTestSchema()
    {
        return [
            'empty allOf' => [
                ['allOf' => []],
                ['required' => [], 'properties' => []]
            ],
            'required' => [
                ['allOf' => [['required' => ['foo']], ['required' => ['bar']]]],
                ['required' => ['foo', 'bar'], 'properties' => []]
            ],
            'properties' => [
                ['allOf' => [
                    ['properties' => ['foo' => ['type' => 'int']]],
                    ['properties' => ['bar' => ['type' => 'date-time']]]
                ]],
                ['required' => [], 'properties' => ['foo' => ['type' => 'int'], 'bar' => ['type' => 'date-time']]]
            ],
            'oneOf inside' => [
                ['allOf' => [
                    ['oneOf' => [
                        ['required' => ['foo'], 'properties' => ['foo' => ['type' => 'int']]],
                        ['required' => ['bar', 'foo'], 'properties' => ['bar' => ['type' => 'date-time'], 'foo' => ['type' => 'string']]]
                    ]]
                ]],
                ['required' => ['foo'], 'properties' => ['bar' => ['type' => 'date-time'], 'foo' => ['type' => 'string']]]
            ],
            'anyOf inside' => [
                ['allOf' => [
                    ['anyOf' => [
                        ['required' => ['foo'], 'properties' => ['foo' => ['type' => 'int']]],
                        ['required' => ['bar'], 'properties' => ['bar' => ['type' => 'date-time']]]
                    ]]
                ]],
                ['required' => [], 'properties' => ['bar' => ['type' => 'date-time'], 'foo' => ['type' => 'int']]]
            ],
        ];
    }

    /**
     * @dataProvider provideTestSchema
     */
    public function testGenerateSubTypes($schema, $subschema)
    {
        $underTest = new IntersectProperty('myPropertyName', $schema, $this->generatorRequest);

        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $underTest->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass(Argument::that(function(GeneratorRequest $subReq) use ($subschema) {
            return Assert::equalTo($subschema)->evaluate($subReq->getSchema());
        }))->shouldHaveBeenCalled();
    }
}
