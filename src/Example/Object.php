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
    private $createdAt = null;

    /**
     * @var string|null $gender
     */
    private $gender = null;

    /**
     * @var string $firstName
     */
    private $firstName = null;

    /**
     * @var string $lastName
     */
    private $lastName = null;

    /**
     * @var string|null $email
     */
    private $email = null;

    /**
     * @var ObjectBilling|null $billing
     */
    private $billing = null;

    /**
     * @var ObjectPaymentAlternative1|ObjectPaymentAlternative2|string|null $payment
     */
    private $payment = null;

    /**
     * @var ObjectAddress|null $address
     */
    private $address = null;

    /**
     * @var string[]|null $tags
     */
    private $tags = null;

    /**
     * @var ObjectHobbiesItem[]|null $hobbies
     */
    private $hobbies = null;

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     * @return self
     */
    public function withCreatedAt($createdAt)
    {
        $clone = clone $this;
        $clone->createdAt = $createdAt;

        return $clone;
    }

    /**
     * @return string|null
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string|null $gender
     * @return self
     */
    public function withGender($gender)
    {
        $clone = clone $this;
        $clone->gender = $gender;

        return $clone;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return self
     */
    public function withFirstName($firstName)
    {
        $clone = clone $this;
        $clone->firstName = $firstName;

        return $clone;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return self
     */
    public function withLastName($lastName)
    {
        $clone = clone $this;
        $clone->lastName = $lastName;

        return $clone;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     * @return self
     */
    public function withEmail($email)
    {
        $clone = clone $this;
        $clone->email = $email;

        return $clone;
    }

    /**
     * @return ObjectBilling|null
     */
    public function getBilling()
    {
        return $this->billing;
    }

    /**
     * @param ObjectBilling|null $billing
     * @return self
     */
    public function withBilling($billing)
    {
        $clone = clone $this;
        $clone->billing = $billing;

        return $clone;
    }

    /**
     * @return ObjectPaymentAlternative1|ObjectPaymentAlternative2|string|null
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param ObjectPaymentAlternative1|ObjectPaymentAlternative2|string|null $payment
     * @return self
     */
    public function withPayment($payment)
    {
        $clone = clone $this;
        $clone->payment = $payment;

        return $clone;
    }

    /**
     * @return ObjectAddress|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param ObjectAddress|null $address
     * @return self
     */
    public function withAddress($address)
    {
        $clone = clone $this;
        $clone->address = $address;

        return $clone;
    }

    /**
     * @return string[]|null
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param string[]|null $tags
     * @return self
     */
    public function withTags($tags)
    {
        $clone = clone $this;
        $clone->tags = $tags;

        return $clone;
    }

    /**
     * @return ObjectHobbiesItem[]|null
     */
    public function getHobbies()
    {
        return $this->hobbies;
    }

    /**
     * @param ObjectHobbiesItem[]|null $hobbies
     * @return self
     */
    public function withHobbies($hobbies)
    {
        $clone = clone $this;
        $clone->hobbies = $hobbies;

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return Object Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input)
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
    public static function validateInput($input, $return = false)
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

    public function __clone()
    {
        $this->createdAt = clone $this->createdAt;
        $this->billing = clone $this->billing;
        $this->payment = clone $this->payment;
        $this->address = clone $this->address;
        $this->hobbies = array_map(function($i) { return clone $i; }, $this->hobbies);
    }


}

