<?php

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorContext;
use PHPUnit\Framework\TestCase;

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


}
