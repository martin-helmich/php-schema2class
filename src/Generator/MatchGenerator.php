<?php

namespace Helmich\Schema2Class\Generator;

class MatchGenerator
{
    private array $arms = [];

    public function __construct(private string $subjectExpr)
    {
    }

    public function addArm(string $conditionExpr, string $returnExpr): void
    {
        $this->arms[$returnExpr][] = $conditionExpr;
        $this->arms[$returnExpr] = array_unique($this->arms[$returnExpr]);
    }

    public function generate(): string
    {
        $code = "match ({$this->subjectExpr}) {\n";

        foreach ($this->arms as $returnExpr => $conditionExprs) {
            $arm  = in_array("default", $conditionExprs) ? "default" : join(", ", $conditionExprs);
            $code .= "    {$arm} => {$returnExpr},\n";
        }

        $code .= "}";

        return $code;
    }
}