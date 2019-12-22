<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator\Property;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class OptionalPropertyDecoratorTest extends TestCase
{

    private OptionalPropertyDecorator $decorator;

    private ObjectProphecy $innerProperty;

    protected function setUp(): void
    {
        $this->innerProperty = $this->prophesize(PropertyInterface::class);
        $this->decorator     = new OptionalPropertyDecorator('myPropertyName', $this->innerProperty->reveal());
    }

    public function testCanHandleSchema()
    {
        assertFalse(OptionalPropertyDecorator::canHandleSchema([]));
    }

    public function testIsComplex()
    {
        $this->innerProperty->isComplex()->shouldBeCalled()->willReturn(false);
        assertFalse($this->decorator->isComplex());
    }

    public function testConvertJsonToType()
    {
        $this->innerProperty->convertJSONToType('variable')->shouldBeCalled()->willReturn('echo "InnerCode";');

        $result = $this->decorator->convertJSONToType('variable');

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

        $result = $this->decorator->convertTypeToJSON('variable');

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

        assertNull($this->decorator->cloneProperty());
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
        assertSame($expected, $this->decorator->cloneProperty());
    }

    public function testGetAnnotationAndHintWithSimpleArray()
    {
        $this->innerProperty->typeAnnotation()->shouldBeCalled()->willReturn('Foo');
        assertSame('Foo|null', $this->decorator->typeAnnotation());

        $this->innerProperty->typeHint("7.2.0")->shouldBeCalled()->willReturn('Foo');
        assertSame('?Foo', $this->decorator->typeHint("7.2.0"));

        $this->innerProperty->typeHint("5.6.0")->shouldBeCalled()->willReturn('Foo');
        assertSame('Foo', $this->decorator->typeHint("5.6.0"));

        $this->innerProperty->typeHint("7.2.0")->shouldBeCalled()->willReturn(null);
        assertSame(null, $this->decorator->typeHint("7.2.0"));

    }

}
