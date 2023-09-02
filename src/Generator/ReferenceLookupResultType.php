<?php
namespace Helmich\Schema2Class\Generator;

enum ReferenceLookupResultType
{
    case TYPE_UNKNOWN;
    case TYPE_CLASS;
    case TYPE_ENUM;
}