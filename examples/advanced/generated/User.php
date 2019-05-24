<?php

namespace Example\Advanced;

class User
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = array(
        'required' => array(
            'firstName',
            'lastName',
        ),
        'properties' => array(
            'createdAt' => array(
                'type' => 'string',
                'format' => 'date-time',
            ),
            'gender' => array(
                'type' => 'string',
                'enum' => array(
                    'male',
                    'female',
                ),
            ),
            'firstName' => array(
                'type' => 'string',
                'minLength' => 2,
            ),
            'lastName' => array(
                'type' => 'string',
            ),
            'email' => array(
                'type' => 'string',
                'format' => 'email',
            ),
            'billing' => array(
                'allOf' => array(
                    array(
                        'required' => array(
                            'vatID',
                        ),
                        'properties' => array(
                            'vatID' => array(
                                'type' => 'string',
                            ),
                            'creditLevel' => array(
                                'type' => 'integer',
                            ),
                        ),
                    ),
                    array(
                        'oneOf' => array(
                            array(
                                'required' => array(
                                    'foo',
                                ),
                                'properties' => array(
                                    'foo' => array(
                                        'type' => 'int',
                                    ),
                                ),
                            ),
                            array(
                                'required' => array(
                                    'bar',
                                ),
                                'properties' => array(
                                    'bar' => array(
                                        'type' => 'string',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'payment' => array(
                'oneOf' => array(
                    array(
                        'required' => array(
                            'type',
                        ),
                        'properties' => array(
                            'type' => array(
                                'type' => 'string',
                                'enum' => array(
                                    'invoice',
                                ),
                            ),
                        ),
                    ),
                    array(
                        'required' => array(
                            'type',
                            'accountNumber',
                        ),
                        'properties' => array(
                            'type' => array(
                                'type' => 'string',
                                'enum' => array(
                                    'debit',
                                ),
                            ),
                            'accountNumber' => array(
                                'type' => 'string',
                            ),
                        ),
                    ),
                    array(
                        'type' => 'string',
                    ),
                ),
            ),
            'address' => array(
                'required' => array(
                    'city',
                    'street',
                ),
                'properties' => array(
                    'city' => array(
                        'type' => 'string',
                        'maxLength' => 32,
                    ),
                    'street' => array(
                        'type' => 'string',
                    ),
                ),
            ),
            'tags' => array(
                'type' => 'array',
                'items' => array(
                    'type' => 'string',
                    'minLength' => 1,
                ),
            ),
            'hobbies' => array(
                'type' => 'array',
                'items' => array(
                    'properties' => array(
                        'name' => array(
                            'type' => 'string',
                        ),
                    ),
                ),
            ),
        ),
    );

    /**
     * @var \DateTime|null
     */
    private $createdAt = null;

    /**
     * @var string|null
     */
    private $gender = null;

    /**
     * @var string
     */
    private $firstName = null;

    /**
     * @var string
     */
    private $lastName = null;

    /**
     * @var string|null
     */
    private $email = null;

    /**
     * @var UserBilling|null
     */
    private $billing = null;

    /**
     * @var UserPaymentAlternative1|UserPaymentAlternative2|string|null
     */
    private $payment = null;

    /**
     * @var UserAddress|null
     */
    private $address = null;

    /**
     * @var string[]|null
     */
    private $tags = null;

    /**
     * @var UserHobbiesItem[]|null
     */
    private $hobbies = null;

    /**
     * @param string $firstName
     * @param string $lastName
     */
    public function __construct(string $firstName, string $lastName)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt() : ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return string|null
     */
    public function getGender() : ?string
    {
        return $this->gender;
    }

    /**
     * @return string
     */
    public function getFirstName() : string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName() : string
    {
        return $this->lastName;
    }

    /**
     * @return string|null
     */
    public function getEmail() : ?string
    {
        return $this->email;
    }

    /**
     * @return UserBilling|null
     */
    public function getBilling() : ?UserBilling
    {
        return $this->billing;
    }

    /**
     * @return UserPaymentAlternative1|UserPaymentAlternative2|string|null
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @return UserAddress|null
     */
    public function getAddress() : ?UserAddress
    {
        return $this->address;
    }

    /**
     * @return string[]|null
     */
    public function getTags() : ?array
    {
        return $this->tags;
    }

    /**
     * @return UserHobbiesItem[]|null
     */
    public function getHobbies() : ?array
    {
        return $this->hobbies;
    }

    /**
     * @param \DateTime $createdAt
     * @return self
     */
    public function withCreatedAt(\DateTime $createdAt) : self
    {
        $clone = clone $this;
        $clone->createdAt = $createdAt;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutCreatedAt() : self
    {
        $clone = clone $this;
        unset($clone->createdAt);

        return $clone;
    }

    /**
     * @param string $gender
     * @return self
     */
    public function withGender(string $gender) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($gender, static::$schema['properties']['gender']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->gender = $gender;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutGender() : self
    {
        $clone = clone $this;
        unset($clone->gender);

        return $clone;
    }

    /**
     * @param string $firstName
     * @return self
     */
    public function withFirstName(string $firstName) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($firstName, static::$schema['properties']['firstName']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->firstName = $firstName;

        return $clone;
    }

    /**
     * @param string $lastName
     * @return self
     */
    public function withLastName(string $lastName) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($lastName, static::$schema['properties']['lastName']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->lastName = $lastName;

        return $clone;
    }

    /**
     * @param string $email
     * @return self
     */
    public function withEmail(string $email) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($email, static::$schema['properties']['email']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->email = $email;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutEmail() : self
    {
        $clone = clone $this;
        unset($clone->email);

        return $clone;
    }

    /**
     * @param UserBilling $billing
     * @return self
     */
    public function withBilling(UserBilling $billing) : self
    {
        $clone = clone $this;
        $clone->billing = $billing;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutBilling() : self
    {
        $clone = clone $this;
        unset($clone->billing);

        return $clone;
    }

    /**
     * @param UserPaymentAlternative1|UserPaymentAlternative2|string $payment
     * @return self
     */
    public function withPayment($payment) : self
    {
        $clone = clone $this;
        $clone->payment = $payment;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutPayment() : self
    {
        $clone = clone $this;
        unset($clone->payment);

        return $clone;
    }

    /**
     * @param UserAddress $address
     * @return self
     */
    public function withAddress(UserAddress $address) : self
    {
        $clone = clone $this;
        $clone->address = $address;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutAddress() : self
    {
        $clone = clone $this;
        unset($clone->address);

        return $clone;
    }

    /**
     * @param string[] $tags
     * @return self
     */
    public function withTags(array $tags) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($tags, static::$schema['properties']['tags']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->tags = $tags;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutTags() : self
    {
        $clone = clone $this;
        unset($clone->tags);

        return $clone;
    }

    /**
     * @param UserHobbiesItem[] $hobbies
     * @return self
     */
    public function withHobbies(array $hobbies) : self
    {
        $clone = clone $this;
        $clone->hobbies = $hobbies;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutHobbies() : self
    {
        $clone = clone $this;
        unset($clone->hobbies);

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return User Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input) : User
    {
        static::validateInput($input);

        $createdAt = null;
        if (isset($input['createdAt'])) {
            $createdAt = new \DateTime($input['createdAt']);
        }
        $gender = null;
        if (isset($input['gender'])) {
            $gender = $input['gender'];
        }
        $firstName = $input['firstName'];
        $lastName = $input['lastName'];
        $email = null;
        if (isset($input['email'])) {
            $email = $input['email'];
        }
        $billing = null;
        if (isset($input['billing'])) {
            $billing = UserBilling::buildFromInput($input['billing']);
        }
        $payment = null;
        if (isset($input['payment'])) {
            if (UserPaymentAlternative1::validateInput($input['payment'], true)) {
                $payment = UserPaymentAlternative1::buildFromInput($input['payment']);
            } else if (UserPaymentAlternative2::validateInput($input['payment'], true)) {
                $payment = UserPaymentAlternative2::buildFromInput($input['payment']);
            } else {
                $payment = $input['payment'];
            }
        }
        $address = null;
        if (isset($input['address'])) {
            $address = UserAddress::buildFromInput($input['address']);
        }
        $tags = null;
        if (isset($input['tags'])) {
            $tags = $input['tags'];
        }
        $hobbies = null;
        if (isset($input['hobbies'])) {
            $hobbies = array_map(function($i) { return UserHobbiesItem::buildFromInput($i); }, $input['hobbies']);
        }

        $obj = new static($firstName, $lastName);
        $obj->createdAt = $createdAt;
        $obj->gender = $gender;
        $obj->email = $email;
        $obj->billing = $billing;
        $obj->payment = $payment;
        $obj->address = $address;
        $obj->tags = $tags;
        $obj->hobbies = $hobbies;
        return $obj;
    }

    /**
     * Converts this object back to a simple array that can be JSON-serialized
     *
     * @return array Converted array
     */
    public function toJson() : array
    {
        $output = [];
        if (isset($this->createdAt)) {
            $output['createdAt'] = $this->createdAt;
        }
        if (isset($this->gender)) {
            $output['gender'] = $this->gender;
        }
        $output['firstName'] = $this->firstName;
        $output['lastName'] = $this->lastName;
        if (isset($this->email)) {
            $output['email'] = $this->email;
        }
        if (isset($this->billing)) {
            $output['billing'] = $this->billing->toJson();
        }
        if (isset($this->payment)) {
            if ($this instanceof UserPaymentAlternative1) {
                $output['payment'] = $this->payment->toJson();
            }
            if ($this instanceof UserPaymentAlternative2) {
                $output['payment'] = $this->payment->toJson();
            }
        }
        if (isset($this->address)) {
            $output['address'] = $this->address->toJson();
        }
        if (isset($this->tags)) {
            $output['tags'] = $this->tags;
        }
        if (isset($this->hobbies)) {
            $output['hobbies'] = array_map(function(UserHobbiesItem $i) { return $i->toJson(); }, $this->hobbies);
        }

        return $output;
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

    public function __clone()
    {
        if (isset($this->createdAt)) {
            $this->createdAt = clone $this->createdAt;
        }
        if (isset($this->billing)) {
            $this->billing = clone $this->billing;
        }
        if (isset($this->payment)) {
            $this->payment = clone $this->payment;
        }
        if (isset($this->address)) {
            $this->address = clone $this->address;
        }
        if (isset($this->tags)) {
            $this->tags = clone $this->tags;
        }
        if (isset($this->hobbies)) {
            $this->hobbies = array_map(function(UserHobbiesItem $i) { return clone $i; }, $this->hobbies);
        }
    }


}

