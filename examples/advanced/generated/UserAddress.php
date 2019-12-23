<?php
declare(strict_types = 1);

namespace Example\Advanced;

class UserAddress
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = array(
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
    );

    /**
     * @var string
     */
    private $city = null;

    /**
     * @var string
     */
    private $street = null;

    /**
     * @param string $city
     * @param string $street
     */
    public function __construct(string $city, string $street)
    {
        $this->city = $city;
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getCity() : string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getStreet() : string
    {
        return $this->street;
    }

    /**
     * @param string $city
     * @return self
     */
    public function withCity(string $city) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($city, static::$schema['properties']['city']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->city = $city;

        return $clone;
    }

    /**
     * @param string $street
     * @return self
     */
    public function withStreet(string $street) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($street, static::$schema['properties']['street']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->street = $street;

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return UserAddress Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input) : UserAddress
    {
        static::validateInput($input);

        $city = $input['city'];
        $street = $input['street'];

        $obj = new static($city, $street);

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
        $output['city'] = $this->city;
        $output['street'] = $this->street;

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

