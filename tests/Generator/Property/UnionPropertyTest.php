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
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

class UnionPropertyTest extends TestCase
{
    use ProphecyTrait;

    private UnionProperty $property;

    private GeneratorRequest $generatorRequest;

    protected function setUp(): void
    {
        $this->generatorRequest = new GeneratorRequest([], new ValidatedSpecificationFilesItem("BarNs", "Foo", ""), new SpecificationOptions());
        $this->property = new UnionProperty(
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
        assertTrue($this->property->isComplex());
    }

    public function testConvertJsonToType()
    {
        $underTest = new UnionProperty('myPropertyName', ['anyOf' => [['properties' => ['subFoo1' => ['type' => 'string']]], ['properties' => ['subFoo2' => ['type' => 'string']]]]], $this->generatorRequest);

        $result = $underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
if ((FooMyPropertyNameAlternative1::validateInput($variable['myPropertyName'], true))) {
    $myPropertyName = FooMyPropertyNameAlternative1::buildFromInput($variable['myPropertyName'], $validate);
} else if ((FooMyPropertyNameAlternative2::validateInput($variable['myPropertyName'], true))) {
    $myPropertyName = FooMyPropertyNameAlternative2::buildFromInput($variable['myPropertyName'], $validate);
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
if (($this->myPropertyName instanceof FooMyPropertyNameAlternative1) || ($this->myPropertyName instanceof FooMyPropertyNameAlternative2)) {
    $variable['myPropertyName'] = ($this->myPropertyName)->toJson();
}
EOCODE;

        assertSame($expected, $result);
    }

    public function testCloneProperty()
    {
        $expected = <<<'EOCODE'
$this->myPropertyName = ($this->myPropertyName instanceof FooMyPropertyNameAlternative2) ? (clone $this->myPropertyName) : (($this->myPropertyName instanceof FooMyPropertyNameAlternative1) ? (clone $this->myPropertyName) : ($this->myPropertyName));
EOCODE;
        assertSame($expected, $this->property->cloneProperty());
    }

    public function testGetAnnotationAndHintWithSimpleArray()
    {
        $underTest = new UnionProperty('myPropertyName', ['anyOf' => [['properties' => ['subFoo1' => ['type' => 'string']]], ['properties' => ['subFoo2' => ['type' => 'string']]]]], $this->generatorRequest);

        assertSame('FooMyPropertyNameAlternative1|FooMyPropertyNameAlternative2', $underTest->typeAnnotation());
        assertSame(null, $underTest->typeHint("7.2.0"));
        assertSame(null, $underTest->typeHint("5.6.0"));
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

        $underTest = new UnionProperty('myPropertyName', $schema, $this->generatorRequest);

        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $underTest->generateSubTypes($schemaToClass->reveal());

        $idx = 0;
        $schemaToClass->schemaToClass(Argument::that(function (GeneratorRequest $subReq) use ($subschemas, &$idx) {
            return Assert::equalTo($subschemas[$idx++])->evaluate($subReq->getSchema());
        }))->shouldHaveBeenCalled();
    }

}
