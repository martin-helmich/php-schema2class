<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class UnionPropertyTest extends TestCase
{

    /** @var UnionProperty */
    private $underTest;

    /** @var GeneratorRequest */
    private $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = new GeneratorRequest([], "", "BarNs", "Foo");
        $this->underTest = new UnionProperty(
            'myPropertyName',
            ['anyOf' => [['properties' => ['subFoo1' => ['type' => 'string']]], ['properties' => ['subFoo2' => ['type' => 'string']]]]],
            $this->generatorRequest
        );
    }

    public function testCanHandleSchema()
    {
        assertTrue(UnionProperty::canHandleSchema(['anyOf' => []]));
        assertTrue(UnionProperty::canHandleSchema(['oneOf' => []]));
    }

    public function testIsComplex()
    {
        assertTrue($this->underTest->isComplex());
    }

    public function testConvertJsonToType()
    {
        $underTest = new UnionProperty('myPropertyName', ['anyOf' => [['properties' => ['subFoo1' => ['type' => 'string']]], ['properties' => ['subFoo2' => ['type' => 'string']]]]], $this->generatorRequest);

        $result = $underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
if (FooMyPropertyNameAlternative1::validateInput($variable['myPropertyName'], true)) {
    $myPropertyName = FooMyPropertyNameAlternative1::buildFromInput($variable['myPropertyName']);
} else if (FooMyPropertyNameAlternative2::validateInput($variable['myPropertyName'], true)) {
    $myPropertyName = FooMyPropertyNameAlternative2::buildFromInput($variable['myPropertyName']);
} else {
    $myPropertyName = $variable['myPropertyName'];
}
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJson()
    {
        $underTest = new UnionProperty('myPropertyName', ['anyOf' => [['properties' => ['subFoo1' => ['type' => 'string']]], ['properties' => ['subFoo2' => ['type' => 'string']]]]], $this->generatorRequest);

        $result = $underTest->convertTypeToJSON('variable');

        $expected = <<<'EOCODE'
if ($this instanceof FooMyPropertyNameAlternative1) {
    $variable['myPropertyName'] = $this->myPropertyName->toJson();
}
if ($this instanceof FooMyPropertyNameAlternative2) {
    $variable['myPropertyName'] = $this->myPropertyName->toJson();
}
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
        $underTest = new UnionProperty('myPropertyName', ['anyOf' => [['properties' => ['subFoo1' => ['type' => 'string']]], ['properties' => ['subFoo2' => ['type' => 'string']]]]], $this->generatorRequest);

        assertSame('FooMyPropertyNameAlternative1|FooMyPropertyNameAlternative2', $underTest->typeAnnotation());
        assertSame(null, $underTest->typeHint(7));
        assertSame(null, $underTest->typeHint(5));
    }

    public function provideTestSchema()
    {
        return [
            'oneOf inside' => [
                ['oneOf' => [
                    ['required' => ['foo'], 'properties' => ['foo' => ['type' => 'int']]],
                    ['required' => ['bar', 'foo'], 'properties' => ['bar' => ['type' => 'date-time'], 'foo' => ['type' => 'string']]]
                ]],
            ],
            'anyOf inside' => [
                ['anyOf' => [
                    ['required' => ['foo'], 'properties' => ['foo' => ['type' => 'int']]],
                    ['required' => ['bar'], 'properties' => ['bar' => ['type' => 'date-time']]]
                ]],
            ],
        ];
    }

    /**
     * @dataProvider provideTestSchema
     */
    public function testGenerateSubTypes($schema)
    {
        if (isset($schema['oneOf'])) {
            $subschemas = $schema['oneOf'];
        } elseif (isset($schema['anyOf'])) {
            $subschemas = $schema['anyOf'];
        }

//        $this->generatorRequest->getTargetClass()->willReturn('');

//        foreach ($subschemas as $i => $subschema) {
//            $this->generatorRequest->withSchema($subschema)->willReturn($this->generatorRequest->reveal());
//            $this->generatorRequest->withClass('MyPropertyNameAlternative'.($i+1))->willReturn($this->generatorRequest->reveal());
//        }

        $underTest = new UnionProperty('myPropertyName', $schema, $this->generatorRequest);

        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $underTest->generateSubTypes($schemaToClass->reveal());

        $idx = 0;
        $schemaToClass->schemaToClass(Argument::that(function (GeneratorRequest $subReq) use ($subschemas, &$idx) {
            return Assert::equalTo($subschemas[$idx++])->evaluate($subReq->getSchema());
        }))->shouldHaveBeenCalled();
    }

}
