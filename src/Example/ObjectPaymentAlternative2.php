<?php

namespace Helmich\JsonStructBuilder\Example;

class ObjectPaymentAlternative2
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array $schema
     */
    private static $schema = [
        'required' => [
            'type',
            'accountNumber',
        ],
        'properties' => [
            'type' => [
                'type' => 'string',
                'enum' => [
                    'debit',
                ],
            ],
            'accountNumber' => [
                'type' => 'string',
            ],
        ],
    ];

    /**
     * @var string $type
     */
    public $type = null;

    /**
     * @var string $accountNumber
     */
    public $accountNumber = null;

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return ObjectPaymentAlternative2 Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input) : \Helmich\JsonStructBuilder\Example\ObjectPaymentAlternative2
    {
        static::validateInput($input);

        $obj = new static;
        $obj->type = $input['type'];
        $obj->accountNumber = $input['accountNumber'];

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

