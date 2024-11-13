<?php

declare(strict_types=1);

namespace Helmich\Schema2Class\Codegen;

use PhpParser\Builder\Enum_;
use PhpParser\Builder\Namespace_;
use PhpParser\Builder\Use_;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

final class AbstractGeneratorTest extends TestCase
{
    public function test_remove_and_set(): void
    {
        $firstNode = (new Namespace_('TestNamespace'))->getNode();
        $secondNode = (new Use_('TestImport', Node\Stmt\Use_::TYPE_NORMAL))->getNode();
        $thirdNode = (new Enum_('TestEnum'))->getNode();
        $setNode = (new Enum_('SetEnum'))->getNode();

        $generator = new class([$firstNode, $secondNode, $thirdNode]) extends AbstractGenerator {};
        $removed = $generator->remove(1);
        $generator->set(1, $setNode);

        $this->assertSame($secondNode, $removed);
        $this->assertSame(2, $generator->count());
        $this->assertSame($firstNode, $generator->first());
        $this->assertSame($firstNode, $generator->get(0));
        $this->assertSame($setNode, $generator->last());
    }

    public function test_insert(): void
    {
        $firstNode = (new Namespace_('TestNamespaceInsert'))->getNode();
        $secondNode = (new Use_('TestImportInsert', Node\Stmt\Use_::TYPE_NORMAL))->getNode();
        $insertNode = (new Enum_('InsertedEnum'))->getNode();

        $generator = new class([$firstNode, $secondNode]) extends AbstractGenerator {};
        $generator->insert(1, $insertNode);

        $this->assertSame(3, $generator->count());
        $this->assertSame($firstNode, $generator->first());
        $this->assertSame($insertNode, $generator->get(1));
        $this->assertSame($secondNode, $generator->last());
    }

    public function test_filter(): void
    {
        $firstNode = (new Namespace_('TestNamespaceInsert'))->getNode();
        $secondNode = (new Use_('TestImportInsert', Node\Stmt\Use_::TYPE_NORMAL))->getNode();
        $thirdNode = (new Enum_('InsertedEnum'))->getNode();

        $generator = new class([$firstNode, $secondNode, $thirdNode]) extends AbstractGenerator {};
        $generator->filter(function (Node $value) {
            return $value instanceof Node\Stmt\Enum_;
        });

        $this->assertSame(1, $generator->count());
        $this->assertSame($thirdNode, $generator->first());
    }

    public function test_walk(): void
    {
        $firstNode = (new Namespace_('TestNamespaceInsert'))->getNode();
        $secondNode = (new Use_('TestImportInsert', Node\Stmt\Use_::TYPE_NORMAL))->getNode();
        $replaceNode = (new Enum_('InsertedEnum'))->getNode();

        $generator = new class([$firstNode, $secondNode]) extends AbstractGenerator {};
        $generator->walk(function (Node &$value) use ($replaceNode) {
            $value = $replaceNode;
        });

        $this->assertSame(2, $generator->count());
        $this->assertSame($replaceNode, $generator->first());
        $this->assertSame($replaceNode, $generator->last());
    }
}