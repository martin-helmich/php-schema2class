<?php

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorContext;
use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use Helmich\Schema2Class\Writer\WriterInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class UnionPropertyTest extends TestCase
{

    /** @var UnionProperty */
    private $underTest;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $generatorContext;

    public function testCanHandleSchema()
    {
        assertTrue(UnionProperty::canHandleSchema(['anyOf' => []]));
        assertTrue(UnionProperty::canHandleSchema(['oneOf' => []]));
    }


    protected function setUp()
    {
        $this->generatorContext = $this->prophesize(GeneratorContext::class);
        $key = 'myPropertyName';
        $this->underTest = new UnionProperty($key, ['anyOf' => [['properties' => ['subFoo1' => ['type' => 'string']]], ['properties' => ['subFoo2' => ['type' => 'string']]]]], $this->generatorContext->reveal());
    }

    public function testIsComplex()
    {
        assertTrue($this->underTest->isComplex());
    }

    public function testConvertJsonToType()
    {
        $ctx = $this->generatorContext->reveal();
        $ctx->request = new \stdClass();
        $ctx->request->targetClass = 'Foo';

        $underTest = new UnionProperty('myPropertyName', ['anyOf' => [['properties' => ['subFoo1' => ['type' => 'string']]], ['properties' => ['subFoo2' => ['type' => 'string']]]]], $ctx);

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
        $ctx = $this->generatorContext->reveal();
        $ctx->request = new \stdClass();
        $ctx->request->targetClass = 'Foo';

        $underTest = new UnionProperty('myPropertyName', ['anyOf' => [['properties' => ['subFoo1' => ['type' => 'string']]], ['properties' => ['subFoo2' => ['type' => 'string']]]]], $ctx);

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
        $ctx = $this->generatorContext->reveal();
        $ctx->request = new \stdClass();
        $ctx->request->targetClass = 'Foo';

        $underTest = new UnionProperty('myPropertyName', ['anyOf' => [['properties' => ['subFoo1' => ['type' => 'string']]], ['properties' => ['subFoo2' => ['type' => 'string']]]]], $ctx);

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
        $generatorRequest = $this->prophesize(GeneratorRequest::class);

        if (isset($schema['oneOf'])) {
            $subschemas = $schema['oneOf'];
        } elseif (isset($schema['anyOf'])) {
            $subschemas = $schema['anyOf'];
        }

        foreach ($subschemas as $i => $subschema) {
            $generatorRequest->withSchema($subschema)->shouldBeCalled()->willReturn($generatorRequest->reveal());
            $generatorRequest->withClass('MyPropertyNameAlternative'.($i+1))->shouldBeCalled()->willReturn($generatorRequest->reveal());
        }

        $consoleOutput = $this->prophesize(OutputInterface::class);
        $writer = $this->prophesize(WriterInterface::class);

        $ctx = $this->generatorContext->reveal();
        $ctx->request = $generatorRequest->reveal();
        $ctx->output = $consoleOutput->reveal();
        $ctx->writer = $writer->reveal();

        $underTest = new UnionProperty('myPropertyName', $schema, $ctx);

        $schemaToClass = $this->prophesize(SchemaToClass::class);

        $underTest->generateSubTypes($schemaToClass->reveal());

        $schemaToClass->schemaToClass($generatorRequest->reveal(), $consoleOutput->reveal(), $writer->reveal())->shouldHaveBeenCalled();
    }

}
