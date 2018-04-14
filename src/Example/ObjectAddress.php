<?php

namespace Helmich\JsonStructBuilder\Example;

class ObjectAddress
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array $schema
     */
    private static $schema = [
        'required' => [
            'city',
            'street',
        ],
        'properties' => [
            'city' => [
                'type' => 'string',
                'maxLength' => 32,
            ],
            'street' => [
                'type' => 'string',
            ],
        ],
    ];

    /**
     * @var string $city
     */
    public $city = null;

    /**
     * @var string $street
     */
    public $street = null;

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return ObjectAddress Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input) : \Helmich\JsonStructBuilder\Example\ObjectAddress
    {
        static::validateInput($input);

        $obj = new static;
        $obj->city = $input['city'];
        $obj->street = $input['street'];

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

