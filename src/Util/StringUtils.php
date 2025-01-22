<?php

namespace Helmich\Schema2Class\Util;

class StringUtils
{
    public static function capitalizeWord(string $word): string
    {
        return strtoupper($word[0]) . substr($word, 1);
    }

    public static function capitalizeName(string $name): string
    {
        return self::capitalizeWord(self::camelCase($name));
    }

    public static function camelCase(string $input): string
    {
        $separatorCharacters = ["-", "_", "/", " "];
        $canonicalizedName = str_replace($separatorCharacters, " ", $input);
        $words = explode(" ", $canonicalizedName);

        $first = $words[0];
        $rest = array_slice($words, 1);

        return $first . join("", array_map(fn (string $w) => self::capitalizeWord($w), $rest));
    }
}