<?php

namespace Helmich\JsonStructBuilder\Example;

class ObjectBilling
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array $schema
     */
    private static $schema = [
        'required' => [
            'vatID',
        ],
        'properties' => [
            'vatID' => [
                'type' => 'string',
            ],
            'foo' => [
                'type' => 'int',
            ],
            'bar' => [
                'type' => 'string',
            ],
        ],
    ];

    /**
     * @var string $vatID
     */
    public $vatID = null;

    /**
     * @var int|null $foo
     */
    public $foo = null;

    /**
     * @var string|null $bar
     */
    public $bar = null;

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return ObjectBilling Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input) : \Helmich\JsonStructBuilder\Example\ObjectBilling
    {
        static::validateInput($input);

        $obj = new static;
        $obj->vatID = $input['vatID'];
        if (isset($input['foo'])) {
            $obj->foo = (int) $input['foo'];
        }
        if (isset($input['bar'])) {
            $obj->bar = $input['bar'];
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

