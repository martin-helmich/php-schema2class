<?php

namespace Helmich\JsonStructBuilder\Example;

class ObjectHobbiesItem
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array $schema
     */
    private static $schema = [
        'properties' => [
            'name' => [
                'type' => 'string',
            ],
        ],
    ];

    /**
     * @var string|null $name
     */
    public $name = null;

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return ObjectHobbiesItem Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input) : \Helmich\JsonStructBuilder\Example\ObjectHobbiesItem
    {
        static::validateInput($input);

        $obj = new static;
        if (isset($input['name'])) {
            $obj->name = $input['name'];
        }

        return $obj;
    }

    /**
     * Validates an input array
     *
     * @param array $input Input data
     * @param bool $return Return instead of throwing errors
     * @return bool Validation result
     * @throws \InvalidArgumentException
     */
    public static function validateInput(array $input, bool $return = false) : bool
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($input, static::$schema);

        if (!$validator->isValid() && !$return) {
            $errors = array_map(function($e) {
                return $e["property"] . ": " . $e["message"];
            }, $validator->getErrors());
            throw new \InvalidArgumentException(join(", ", $errors));
        }

        return $validator->isValid();
    }


}

