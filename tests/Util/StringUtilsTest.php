<?php

namespace Helmich\Schema2Class\Util;

use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\equalTo;

class StringUtilsTest extends TestCase
{
    public function testCapitalizeWordCapitalizesWord()
    {
        $capitalized = StringUtils::capitalizeWord("foo");
        assertThat($capitalized, equalTo("Foo"));
    }

    public function testCapitalizeWordDoesNotModifyAlreadyCapitalizedWord()
    {
        $capitalized = StringUtils::capitalizeWord("Foo");
        assertThat($capitalized, equalTo("Foo"));
    }

    public function testCapitalizeNameCapitalizesName()
    {
        $capitalized = StringUtils::capitalizeName("foo");
        assertThat($capitalized, equalTo("Foo"));
    }

    public function testCapitalizeNameCapitalizedWordsWithDashes()
    {
        $capitalized = StringUtils::capitalizeName("content-disposition");
        assertThat($capitalized, equalTo("ContentDisposition"));
    }
}