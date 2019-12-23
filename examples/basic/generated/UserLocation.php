<?php
declare(strict_types = 1);

namespace Example\Basic;

class UserLocation
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = array(
        'properties' => array(
            'country' => array(
                'type' => 'string',
            ),
            'city' => array(
                'type' => 'string',
            ),
        ),
    );

    /**
     * @var string|null
     */
    private $country = null;

    /**
     * @var string|null
     */
    private $city = null;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @return string|null
     */
    public function getCountry() : ?string
    {
        return $this->country;
    }

    /**
     * @return string|null
     */
    public function getCity() : ?string
    {
        return $this->city;
    }

    /**
     * @param string $country
     * @return self
     */
    public function withCountry(string $country) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($country, static::$schema['properties']['country']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->country = $country;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutCountry() : self
    {
        $clone = clone $this;
        unset($clone->country);

        return $clone;
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
     * @return self
     */
    public function withoutCity() : self
    {
        $clone = clone $this;
        unset($clone->city);

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return UserLocation Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input) : UserLocation
    {
        static::validateInput($input);

        $country = null;
        if (isset($input['country'])) {
            $country = $input['country'];
        }
        $city = null;
        if (isset($input['city'])) {
            $city = $input['city'];
        }

        $obj = new static();
        $obj->country = $country;
        $obj->city = $city;
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
        if (isset($this->country)) {
            $output['country'] = $this->country;
        }
        if (isset($this->city)) {
            $output['city'] = $this->city;
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
    }


}

