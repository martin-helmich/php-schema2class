<?php

declare(strict_types=1);

namespace Ns\Definitions\Definitions;

class Address
{
    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static array $schema = [
        'type' => 'object',
        'properties' => [
            'name' => [
                '$ref' => '#/definitions/address/$defs/name',
            ],
            'city' => [
                'type' => 'string',
            ],
        ],
        'required' => [
            'city',
        ],
        '$defs' => [
            'name' => [
                'type' => 'object',
                'properties' => [
                    'first' => [
                        'type' => 'string',
                    ],
                ],
            ],
        ],
    ];

    /**
     * @var Address\Defs\Name|null
     */
    private ?Address\Defs\Name $name = null;

    /**
     * @var string
     */
    private string $city;

    /**
     * @param string $city
     */
    public function __construct(string $city)
    {
        $this->city = $city;
    }

    /**
     * @return Address\Defs\Name|null
     */
    public function getName() : ?Address\Defs\Name
    {
        return $this->name ?? null;
    }

    /**
     * @return string
     */
    public function getCity() : string
    {
        return $this->city;
    }

    /**
     * @param Address\Defs\Name $name
     * @return self
     */
    public function withName(Address\Defs\Name $name) : self
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutName() : self
    {
        $clone = clone $this;
        unset($clone->name);

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
     * Builds a new instance from an input array
     *
     * @param array|object $input Input data
     * @param bool $validate Set this to false to skip validation; use at own risk
     * @return Address Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array|object $input, bool $validate = true) : Address
    {
        $input = is_array($input) ? \JsonSchema\Validator::arrayToObjectRecursive($input) : $input;
        if ($validate) {
            static::validateInput($input);
        }

        $name = null;
        if (isset($input->{'name'})) {
            $name = Address\Defs\Name::buildFromInput($input->{'name'}, validate: $validate);
        }
        $city = $input->{'city'};

        $obj = new self($city);
        $obj->name = $name;
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
        if (isset($this->name)) {
            $output['name'] = $this->name->toJson();
        }
        $output['city'] = $this->city;

        return $output;
    }

    /**
     * Validates an input array
     *
     * @param array|object $input Input data
     * @param bool $return Return instead of throwing errors
     * @return bool Validation result
     * @throws \InvalidArgumentException
     */
    public static function validateInput(array|object $input, bool $return = false) : bool
    {
        $validator = new \JsonSchema\Validator();
        $input = is_array($input) ? \JsonSchema\Validator::arrayToObjectRecursive($input) : $input;
        $validator->validate($input, static::$schema);

        if (!$validator->isValid() && !$return) {
            $errors = array_map(function(array $e): string {
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