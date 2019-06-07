<?php

namespace Helmich\Schema2Class\Generator;

use PHPUnit\Framework\TestCase;

class GeneratorRequestTest extends TestCase
{

    const TARGET_DIR = 'targetDir';
    const TARGET_NAME_SPACE = 'targetNameSpace';
    const TARGET_CLASS_NAME = 'targetClassName';

    /** @var GeneratorRequest */
    private $underTest;

    protected function setUp()
    {
        $this->underTest = new GeneratorRequest([], self::TARGET_DIR, self::TARGET_NAME_SPACE, self::TARGET_CLASS_NAME);
    }

    public function testIsPhp()
    {
        $this->underTest->php5 = false;

        assertTrue($this->underTest->isPhp(7));
        assertFalse($this->underTest->isPhp(5));

        $this->underTest->php5 = true;

        assertTrue($this->underTest->isPhp(5));
        assertFalse($this->underTest->isPhp(7));

    }

    public function testGetTargetNamespace()
    {
        assertSame(self::TARGET_NAME_SPACE, $this->underTest->getTargetNamespace());
    }

    public function testWithClass()
    {
        $underTest = $this->underTest->withClass('Foo');

        assertNotSame($underTest, $this->underTest);
        assertSame('Foo', $underTest->getTargetClass());
        assertSame(self::TARGET_CLASS_NAME, $this->underTest->getTargetClass());
    }

    public function testWithSchema()
    {
        $schema = ['properties' => ['Foo']];

        $underTest = $this->underTest->withSchema($schema);

        assertNotSame($underTest, $this->underTest);
        assertSame($schema, $underTest->getSchema());
        assertSame([], $this->underTest->getSchema());
    }

    public function testGetPhpTargetVersion()
    {
        $this->underTest->php5 = false;
        assertSame(7, $this->underTest->getPhpTargetVersion());

        $this->underTest->php5 = true;
        assertSame(5, $this->underTest->getPhpTargetVersion());
    }

    public function testGetTargetDirectory()
    {
        assertSame(self::TARGET_DIR, $this->underTest->getTargetDirectory());
    }
}
