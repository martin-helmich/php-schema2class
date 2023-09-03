<?php

namespace Helmich\Schema2Class\Generator;

readonly class ReferencedTypeClass implements ReferencedType
{
    public function __construct(private string $className)
    {
    }

    public function name(): string
    {
        return $this->className;
    }

    public function typeAnnotation(GeneratorRequest $req): string
    {
        return "\\" . $this->className;
    }

    public function typeHint(GeneratorRequest $req): ?string
    {
        return "\\" . $this->className;
    }

    public function serializedTypeHint(GeneratorRequest $req): ?string
    {
        return "array";
    }

    public function typeAssertionExpr(GeneratorRequest $req, string $expr): string
    {
        return "({$expr}) instanceof \\{$this->className}";
    }

    public function inputAssertionExpr(GeneratorRequest $req, string $expr): string
    {
        return "\\{$this->className}::validateInput({$expr}, true)";
    }

    public function inputMappingExpr(GeneratorRequest $req, string $expr): string
    {
        if ($req->isAtLeastPHP("8.0")) {
            return "\\{$this->className}::buildFromInput({$expr}, validate: \$validate)";
        }
        return "\\{$this->className}::buildFromInput({$expr}, \$validate)";
    }

    public function outputMappingExpr(GeneratorRequest $req, string $expr): string
    {
        return "{$expr}->toJson()";
    }

}