<?php

declare (strict_types=1);

namespace Ns\EnumConsistent;

enum Foo : string
{
    case VALUE_Foo = 'Foo';
    case VALUE_1Foo = '1Foo';
}