<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

trait CodeFormatting
{
    protected function indentCode(string $code, int $by = 1): string
    {
        $indent = str_repeat("    ", $by);
        $lines = explode("\n", $code);
        $lines = array_map(function($l) use ($indent) {
            return $indent . $l;
        }, $lines);

        return join("\n", $lines);
    }

    protected function capitalize(string $str): string
    {
        return strtoupper($str[0]) . substr($str, 1);
    }
}