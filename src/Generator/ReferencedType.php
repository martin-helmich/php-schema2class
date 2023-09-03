<?php

namespace Helmich\Schema2Class\Generator;

interface ReferencedType
{
    function name(): string;
    function typeAnnotation(GeneratorRequest $req): string;
    function typeHint(GeneratorRequest $req): ?string;
    function serializedTypeHint(GeneratorRequest $req): ?string;
    function typeAssertionExpr(GeneratorRequest $req, string $expr): string;
    function inputAssertionExpr(GeneratorRequest $req, string $expr): string;
    function inputMappingExpr(GeneratorRequest $req, string $expr): string;
    function outputMappingExpr(GeneratorRequest $req, string $expr): string;
}