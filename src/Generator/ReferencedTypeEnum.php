<?php

namespace Helmich\Schema2Class\Generator;

readonly class ReferencedTypeEnum implements ReferencedType
{
    public function __construct(private string $enumName)
    {
    }

    function name(): string
    {
        return $this->enumName;
    }

    public function typeAnnotation(GeneratorRequest $req): string
    {
        return "\\" . $this->enumName;
    }

    public function typeHint(GeneratorRequest $req): ?string
    {
        return "\\" . $this->enumName;
    }

    public function serializedTypeHint(GeneratorRequest $req): ?string
    {
        return "string";
    }

    public function typeAssertionExpr(GeneratorRequest $req, string $expr): string
    {
        return "({$expr}) instanceof \\{$this->enumName}";
    }

    public function inputAssertionExpr(GeneratorRequest $req, string $expr): string
    {
        return "\\{$this->enumName}::tryFrom({$expr}) !== null";
    }

    public function inputMappingExpr(GeneratorRequest $req, string $expr, ?string $validateExpr): string
    {
        return "\\{$this->enumName}::from({$expr})";
    }

    public function outputMappingExpr(GeneratorRequest $req, string $expr): string
    {
        return "{$expr}->value";
    }

}