<?php

namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorContext;
use PHPUnit\Framework\TestCase;

class DatePropertyTest extends TestCase
{

    /**
     * @var DateProperty
     */
    private $underTest;

    public function testCanHandleSchema()
    {
        assertTrue(DateProperty::canHandleSchema(['type' => 'string', 'format' => 'date-time']));

        assertFalse(DateProperty::canHandleSchema(['type' => 'string']));
        assertFalse(DateProperty::canHandleSchema(['type' => 'string', 'format' => 'foo']));

    }
    protected function setUp()
    {
        $this->generatorContext = $this->prophesize(GeneratorContext::class);
        $key = 'myPropertyName';
        $this->underTest = new DateProperty($key, ['type' => 'string', 'format' => 'date-time'], $this->generatorContext->reveal());
    }

    public function testIsComplex()
    {
        assertTrue($this->underTest->isComplex());
    }

    public function testConvertJsonToType()
    {
        $result = $this->underTest->convertJSONToType('variable');

        $expected = <<<'EOCODE'
$myPropertyName = new \DateTime($variable['myPropertyName']);
EOCODE;

        assertSame($expected, $result);
    }

    public function testConvertTypeToJson()
    {
        $result = $this->underTest->convertTypeToJSON('variable');

        $expected = <<<'EOCODE'
$variable['myPropertyName'] = $this->myPropertyName;
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
