<?php

namespace Example\Advanced;

class UserPaymentAlternative2
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = array(
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
    );

    /**
     * @var string
     */
    private $type = null;

    /**
     * @var string
     */
    private $accountNumber = null;

    /**
     * @param string $type
     * @param string $accountNumber
     */
    public function __construct(string $type, string $accountNumber)
    {
        $this->type = $type;
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getAccountNumber() : string
    {
        return $this->accountNumber;
    }

    /**
     * @param string $type
     * @return self
     */
    public function withType(string $type) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($type, static::$schema['properties']['type']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->type = $type;

        return $clone;
    }

    /**
     * @param string $accountNumber
     * @return self
     */
    public function withAccountNumber(string $accountNumber) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($accountNumber, static::$schema['properties']['accountNumber']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->accountNumber = $accountNumber;

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return UserPaymentAlternative2 Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input) : UserPaymentAlternative2
    {
        static::validateInput($input);

        $type = $input['type'];
        $accountNumber = $input['accountNumber'];

        $obj = new static($type, $accountNumber);

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
        $output['type'] = $this->type;
        $output['accountNumber'] = $this->accountNumber;

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
    }


}

