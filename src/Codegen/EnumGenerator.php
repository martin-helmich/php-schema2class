<?php

declare(strict_types=1);

namespace Helmich\Schema2Class\Codegen;

use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\PrettyPrinter\Standard;

class EnumGenerator extends AbstractGenerator
{
    /** @var array<string, EnumCase> */
    protected array $cases = [];

    public function __construct(
        protected Enum_ $enum_,
        protected Namespace_ $namespace_
    )
    {
        parent::__construct([
            $this->buildStrictTypes(),
            new Nop(),
            $this->namespace_,
            $this->enum_,
        ]);
    }

    public function withEnum_(Enum_ $enum_): self
    {
        foreach ($this->cases as $name => $case) {
            $enum_->stmts[$name] = $case;
        }

        $currentEnumIndex = $this->find($this->enum_);
        if ($currentEnumIndex !== null) {
            $this->set($currentEnumIndex, $enum_);
        }

        $this->enum_ = $enum_;

        return $this;
    }

    public function withNamespace_(Namespace_ $namespace_): self
    {
        $currentNamespaceIndex = $this->find($this->namespace_);
        if ($currentNamespaceIndex !== null) {
            $this->set($currentNamespaceIndex, $namespace_);
        }

        $this->namespace_ = $namespace_;

        return $this;
    }

    public function withAdditionalEnumCase(EnumCase $enumCase): self
    {
        $this->cases[$enumCase->name->toLowerString()] = $enumCase;
        $this->enum_->stmts[$enumCase->name->toString()] = $enumCase;

        return $this;
    }

    protected function buildStrictTypes(): Declare_
    {
        $declareDeclare = new DeclareDeclare(new Identifier('strict_types'), new LNumber(1));
        return new Declare_([$declareDeclare]);
    }

    public function generate(): string
    {
        $prettyPrinter = new Standard();
        return $prettyPrinter->prettyPrint($this->nodes);
    }
}
