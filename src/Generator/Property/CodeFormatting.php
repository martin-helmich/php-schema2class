<?php
namespace Helmich\Schema2Class\Generator\Property;

trait CodeFormatting
{
    protected function indentCode($code, $by = 1)
    {
        $indent = str_repeat("    ", $by);
        $lines = explode("\n", $code);
        $lines = array_map(function($l) use ($indent) {
            return $indent . $l;
        }, $lines);

        return join("\n", $lines);
    }

    protected function capitalize($str)
    {
        return strtoupper($str[0]) . substr($str, 1);
    }
}