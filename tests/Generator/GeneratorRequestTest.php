<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Generator;

use Helmich\Schema2Class\Spec\SpecificationOptions;
use Helmich\Schema2Class\Spec\ValidatedSpecificationFilesItem;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotSame;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

class GeneratorRequestTest extends TestCase
{

    const TARGET_DIR = 'targetDir';
    const TARGET_NAMESPACE = 'targetNameSpace';
    const TARGET_CLASS_NAME = 'targetClassName';

    private GeneratorRequest $request;

    protected function setUp(): void
    {
        $this->request = new GeneratorRequest(
            [],
            new ValidatedSpecificationFilesItem(
                self::TARGET_NAMESPACE,
                self::TARGET_CLASS_NAME,
                self::TARGET_DIR,
            ),
            (new SpecificationOptions())->withTargetPHPVersion("7.0")
        );
    }

    #[TestDox("is PHP 7")]
    public function testIsPHP7()
    {
        $req = $this->request->withPHPVersion("7.1");

        assertTrue($req->isPhp(7));
        assertFalse($req->isPhp(5));
    }

    #[TestDox("is PHP 5")]
    public function testIsPHP5()
    {
        $req = $this->request->withPHPVersion("5.6");

        assertTrue($req->isPhp(5));
        assertFalse($req->isPhp(7));

    }

    public function testGetTargetNamespace()
    {
        assertSame(self::TARGET_NAMESPACE, $this->request->getTargetNamespace());
    }

    public function testWithClass()
    {
        $underTest = $this->request->withClass('Foo');

        assertNotSame($underTest, $this->request);
        assertSame('Foo', $underTest->getTargetClass());
        assertSame(self::TARGET_CLASS_NAME, $this->request->getTargetClass());
    }

    public function testWithSchema()
    {
        $schema = ['properties' => ['Foo']];

        $underTest = $this->request->withSchema($schema);

        assertNotSame($underTest, $this->request);
        assertSame($schema, $underTest->getSchema());
        assertSame([], $this->request->getSchema());
    }

    public function testGetPhpTargetVersion()
    {
        $req = $this->request->withPHPVersion("7.2");
        assertSame("7.2.0", $req->getTargetPHPVersion());

        $req = $this->request->withPHPVersion("5.6.1");
        assertSame("5.6.1", $req->getTargetPHPVersion());
    }

    public function testGetTargetDirectory()
    {
        assertSame(self::TARGET_DIR, $this->request->getTargetDirectory());
    }
}
