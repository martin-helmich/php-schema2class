<?php

namespace Helmich\Schema2Class\Generator;

readonly class ReferencedTypeUnknown implements ReferencedType
{
    public function name(): string
    {
        return "unknown";
    }

    public function typeAnnotation(GeneratorRequest $req): string
    {
        return "mixed";
    }

    public function typeHint(GeneratorRequest $req): ?string
    {
        if ($req->isAtLeastPHP("8.0")) {
            return "mixed";
        }

        return null;
    }

    public function typeAssertionExpr(GeneratorRequest $req, string $expr): string
    {
        return "true";
    }

    public function inputAssertionExpr(GeneratorRequest $req, string $expr): string
    {
        return "true";
    }

    public function inputMappingExpr(GeneratorRequest $req, string $expr): string
    {
        return $expr;
    }

    public function outputMappingExpr(GeneratorRequest $req, string $expr): string
    {
        return $expr;
    }

}