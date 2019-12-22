<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class IntersectPropertyTest extends TestCase
{

    /** @var IntersectProperty */
    private $underTest;

    /** @var GeneratorRequest */
    private $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = new GeneratorRequest([], "", "BarNs", "Foo");
        $key = 'myPropertyName';
        $this->underTest = new IntersectProperty($key, ['allOf' => []], $this->generatorRequest);
    }

    public function testCanHandleSchema()
    {
        assertTrue(IntersectProperty::canHandleSchema(['allOf' => []]));

        assertFalse(IntersectProperty::canHandleSchema([]));
    }

    public function testIsComplex()
    {
        assertTrue($this->underTest->isComplex());
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
//        $this->generatorRequest->getTargetClass()->willReturn('Foo');
//        $this->generatorRequest->getTargetNamespace()->willReturn('BarNs');

        $underTest = new IntersectProperty('myPropertyName', ['allOf' => []], $this->generatorRequest);

        assertSame('FooMyPropertyName', $underTest->typeAnnotation());
        assertSame('\\BarNs\\FooMyPropertyName', $underTest->typeHint(7));
        assertSame('\\BarNs\\FooMyPropertyName', $underTest->typeHint(5));
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
