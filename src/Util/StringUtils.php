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
        $separatorCharacters = ["-", "_", "/", " "];
        $canonicalizedName = str_replace($separatorCharacters, " ", $name);
        $words = explode(" ", $canonicalizedName);

        return join("", array_map(fn (string $w) => self::capitalizeWord($w), $words));
    }
}