<?php

namespace Helmich\JsonStructBuilder\Example;

class Object
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array $schema
     */
    private static $schema = [
        'required' => [
            'firstName',
            'lastName',
        ],
        'properties' => [
            'createdAt' => [
                'type' => 'string',
                'format' => 'date-time',
            ],
            'gender' => [
                'type' => 'string',
                'enum' => [
                    'male',
                    'female',
                ],
            ],
            'firstName' => [
                'type' => 'string',
                'minLength' => 2,
            ],
            'lastName' => [
                'type' => 'string',
            ],
            'email' => [
                'type' => 'string',
                'format' => 'email',
            ],
            'billing' => [
                'allOf' => [
                    [
                        'required' => [
                            'vatID',
                        ],
                        'properties' => [
                            'vatID' => [
                                'type' => 'string',
                            ],
                        ],
                    ],
                    [
                        'oneOf' => [
                            [
                                'required' => [
                                    'foo',
                                ],
                                'properties' => [
                                    'foo' => [
                                        'type' => 'int',
                                    ],
                                ],
                            ],
                            [
                                'required' => [
                                    'bar',
                                ],
                                'properties' => [
                                    'bar' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'payment' => [
                'oneOf' => [
                    [
                        'required' => [
                            'type',
                        ],
                        'properties' => [
                            'type' => [
                                'type' => 'string',
                                'enum' => [
                                    'invoice',
                                ],
                            ],
                        ],
                    ],
                    [
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
                    ],
                    [
                        'type' => 'string',
                    ],
                ],
            ],
            'address' => [
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
            ],
            'tags' => [
                'type' => 'array',
                'items' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
            ],
            'hobbies' => [
                'type' => 'array',
                'items' => [
                    'properties' => [
                        'name' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ],
    ];

    /**
     * @var \DateTime|null $createdAt
     */
    public $createdAt = null;

    /**
     * @var string|null $gender
     */
    public $gender = null;

    /**
     * @var string $firstName
     */
    public $firstName = null;

    /**
     * @var string $lastName
     */
    public $lastName = null;

    /**
     * @var string|null $email
     */
    public $email = null;

    /**
     * @var ObjectBilling|null $billing
     */
    public $billing = null;

    /**
     * @var ObjectPaymentAlternative1|ObjectPaymentAlternative2|string|null $payment
     */
    public $payment = null;

    /**
     * @var ObjectAddress|null $address
     */
    public $address = null;

    /**
     * @var string[]|null $tags
     */
    public $tags = null;

    /**
     * @var ObjectHobbiesItem[]|null $hobbies
     */
    public $hobbies = null;

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return Object Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input) : \Helmich\JsonStructBuilder\Example\Object
    {
        static::validateInput($input);

        $obj = new static;
        if (isset($input['createdAt'])) {
            $obj->createdAt = new \DateTime($input['createdAt']);
        }
        if (isset($input['gender'])) {
            $obj->gender = $input['gender'];
        }
        $obj->firstName = $input['firstName'];
        $obj->lastName = $input['lastName'];
        if (isset($input['email'])) {
            $obj->email = $input['email'];
        }
        if (isset($input['billing'])) {
            $obj->billing = ObjectBilling::buildFromInput($input['billing']);
        }
        if (isset($input['payment'])) {
            $obj->payment = $input['payment'];
        if (ObjectPaymentAlternative1::validateInput($input['payment'], true)) { $obj->payment = ObjectPaymentAlternative1::buildFromInput($input['payment']); }
        if (ObjectPaymentAlternative2::validateInput($input['payment'], true)) { $obj->payment = ObjectPaymentAlternative2::buildFromInput($input['payment']); }
        }
        if (isset($input['address'])) {
            $obj->address = ObjectAddress::buildFromInput($input['address']);
        }
        if (isset($input['tags'])) {
            $obj->tags = $input['tags'];
        }
        if (isset($input['hobbies'])) {
            $obj->hobbies = array_map(function($i) { return ObjectHobbiesItem::buildFromInput($i); }, $input["hobbies"]);
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

