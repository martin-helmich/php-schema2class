<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

trait CodeFormatting
{
    protected function indentCode(string $code, int $by = 1): string
    {
        $indent = str_repeat("    ", $by);
        $lines = explode("\n", $code);
        $lines = array_map(fn($l) => $indent . $l, $lines);

        return join("\n", $lines);
    }

    protected function convertToCamelCase(string $str): string
    {
        $parts = explode("_", $str);
        $parts = array_map(fn($p) => $this->capitalize($p), $parts);

        return join("", $parts);
    }

    protected function convertToLowerCamelCase(string $str): string
    {
        $parts = explode("_", $str);
        $first = array_shift($parts);
        $parts = array_map(fn($p) => $this->capitalize($p), $parts);

        return $first . join("", $parts);
    }

    protected function capitalize(string $str): string
    {
        return strtoupper($str[0]) . substr($str, 1);
    }
}
