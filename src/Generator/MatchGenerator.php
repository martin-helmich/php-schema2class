<?php

namespace Helmich\Schema2Class\Generator;

class MatchGenerator
{
    private array $arms = [];
    private ?string $defaultArm = null;

    public function __construct(private string $subjectExpr)
    {
    }

    public function addArm(string $conditionExpr, string $returnExpr): void
    {
        if ($conditionExpr === "default") {
            $this->defaultArm = $returnExpr;
            return;
        }
        $this->arms[$returnExpr][] = $conditionExpr;
    }

    public function generate(): string
    {
        $code = "match ({$this->subjectExpr}) {\n";

        foreach ($this->arms as $returnExpr => $conditionExprs) {
            $arm  = join(", ", $conditionExprs);
            $code .= "    {$arm} => {$returnExpr},\n";
        }

        if ($this->defaultArm !== null) {
            $code .= "    default => {$this->defaultArm},\n";
        }

        $code .= "}";

        return $code;
    }
}