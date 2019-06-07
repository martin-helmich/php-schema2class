<?php

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorContext;
use PHPUnit\Framework\TestCase;

class OptionalPropertyDecoratorTest extends TestCase
{

    /** @var OptionalPropertyDecorator */
    private $underTest;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $innerProperty;

    public function testCanHandleSchema()
    {
        assertFalse(OptionalPropertyDecorator::canHandleSchema([]));
    }

    protected function setUp()
    {
        $this->innerProperty = $this->prophesize(PropertyInterface::class);
        $key = 'myPropertyName';
        $this->underTest = new OptionalPropertyDecorator($key, $this->innerProperty->reveal());
    }

    public function testIsComplex()
    {
        $this->innerProperty->isComplex()->shouldBeCalled()->willReturn(false);
        assertFalse($this->underTest->isComplex());
    }

    public function testConvertJsonToType()
    {
        $this->innerProperty->convertJSONToType('variable')->shouldBeCalled()->willReturn('echo "InnerCode";');

        $result = $this->underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = null;
if (isset($variable['myPropertyName'])) {
    echo "InnerCode";
}
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJson()
    {
        $this->innerProperty->convertTypeToJSON('variable')->shouldBeCalled()->willReturn('echo "InnerCode";');

        $result = $this->underTest->convertTypeToJSON('variable');

        $expected = <<<'EOCODE'
if (isset($this->myPropertyName)) {
    echo "InnerCode";
}
EOCODE;

        assertSame($expected, $result);
    }

    public function testClonePropertyWithoutInnerCode()
    {
        $this->innerProperty->key()->shouldBeCalled()->willReturn('innerPropertyName');
        $this->innerProperty->cloneProperty()->shouldBeCalled()->willReturn(null);

        assertNull($this->underTest->cloneProperty());
    }

    public function testClonePropertyWithInnerCode()
    {
        $this->innerProperty->key()->shouldBeCalled()->willReturn('innerPropertyName');
        $this->innerProperty->cloneProperty()->shouldBeCalled()->willReturn('echo "InnerCode";');
        $expected = <<<'EOCODE'
if (isset($this->innerPropertyName)) {
    echo "InnerCode";
}
EOCODE;
        assertSame($expected, $this->underTest->cloneProperty());
    }

    public function testGetAnnotationAndHintWithSimpleArray()
    {
        $this->innerProperty->typeAnnotation()->shouldBeCalled()->willReturn('Foo');
        assertSame('Foo|null', $this->underTest->typeAnnotation());

        $this->innerProperty->typeHint(7)->shouldBeCalled()->willReturn('Foo');
        assertSame('?Foo', $this->underTest->typeHint(7));

        $this->innerProperty->typeHint(5)->shouldBeCalled()->willReturn('Foo');
        assertSame('Foo', $this->underTest->typeHint(5));

        $this->innerProperty->typeHint(7)->shouldBeCalled()->willReturn(null);
        assertSame(null, $this->underTest->typeHint(7));

    }

}
