<?php
declare(strict_types = 1);

namespace Example\Advanced;

class UserHobbiesItem
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = array(
        'properties' => array(
            'name' => array(
                'type' => 'string',
            ),
        ),
    );

    /**
     * @var string|null
     */
    private $name = null;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @return string|null
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function withName(string $name) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($name, static::$schema['properties']['name']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

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
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return UserHobbiesItem Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input) : UserHobbiesItem
    {
        static::validateInput($input);

        $name = null;
        if (isset($input['name'])) {
            $name = $input['name'];
        }

        $obj = new static();
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
            $output['name'] = $this->name;
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

