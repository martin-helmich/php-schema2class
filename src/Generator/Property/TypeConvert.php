<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

trait TypeConvert
{
    protected function phpPrimitiveForSchemaType(array $def): array
    {
        $t = isset($def["type"]) ? $def["type"] : "any";

        if ($t === "string") {
            if (isset($def["format"]) && $def["format"] == "date-time") {
                return ["\\DateTime", "\\DateTime"];
            }

            return ["string", "string"];
        } else if ($t === "integer" || $t === "int") {
            return ["int", "int"];
        } else if ($t === "number") {
            if (isset($def["format"]) && $def["format"] === "integer") {
                return ["int", "int"];
            }

            return ["float", "float"];
        }

        return ["mixed", null];
    }
}