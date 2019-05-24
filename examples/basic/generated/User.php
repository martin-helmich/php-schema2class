<?php

namespace Example\Basic;

class User
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = array(
        'required' => array(
            'givenName',
            'familyName',
        ),
        'properties' => array(
            'givenName' => array(
                'type' => 'string',
            ),
            'familyName' => array(
                'type' => 'string',
            ),
            'hobbies' => array(
                'type' => 'array',
                'items' => array(
                    'type' => 'string',
                ),
            ),
            'location' => array(
                'properties' => array(
                    'country' => array(
                        'type' => 'string',
                    ),
                    'city' => array(
                        'type' => 'string',
                    ),
                ),
            ),
        ),
    );

    /**
     * @var string
     */
    private $givenName = null;

    /**
     * @var string
     */
    private $familyName = null;

    /**
     * @var string[]|null
     */
    private $hobbies = null;

    /**
     * @var UserLocation|null
     */
    private $location = null;

    /**
     * @param string $givenName
     * @param string $familyName
     */
    public function __construct(string $givenName, string $familyName)
    {
        $this->givenName = $givenName;
        $this->familyName = $familyName;
    }

    /**
     * @return string
     */
    public function getGivenName() : string
    {
        return $this->givenName;
    }

    /**
     * @return string
     */
    public function getFamilyName() : string
    {
        return $this->familyName;
    }

    /**
     * @return string[]|null
     */
    public function getHobbies() : ?array
    {
        return $this->hobbies;
    }

    /**
     * @return UserLocation|null
     */
    public function getLocation() : ?UserLocation
    {
        return $this->location;
    }

    /**
     * @param string $givenName
     * @return self
     */
    public function withGivenName(string $givenName) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($givenName, static::$schema['properties']['givenName']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->givenName = $givenName;

        return $clone;
    }

    /**
     * @param string $familyName
     * @return self
     */
    public function withFamilyName(string $familyName) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($familyName, static::$schema['properties']['familyName']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->familyName = $familyName;

        return $clone;
    }

    /**
     * @param string[] $hobbies
     * @return self
     */
    public function withHobbies(array $hobbies) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($hobbies, static::$schema['properties']['hobbies']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

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
     * @param UserLocation $location
     * @return self
     */
    public function withLocation(UserLocation $location) : self
    {
        $clone = clone $this;
        $clone->location = $location;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutLocation() : self
    {
        $clone = clone $this;
        unset($clone->location);

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

        $givenName = $input['givenName'];
        $familyName = $input['familyName'];
        $hobbies = null;
        if (isset($input['hobbies'])) {
            $hobbies = $input['hobbies'];
        }
        $location = null;
        if (isset($input['location'])) {
            $location = UserLocation::buildFromInput($input['location']);
        }

        $obj = new static($givenName, $familyName);
        $obj->hobbies = $hobbies;
        $obj->location = $location;
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
        $output['givenName'] = $this->givenName;
        $output['familyName'] = $this->familyName;
        if (isset($this->hobbies)) {
            $output['hobbies'] = $this->hobbies;
        }
        if (isset($this->location)) {
            $output['location'] = $this->location->toJson();
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
        if (isset($this->hobbies)) {
            $this->hobbies = clone $this->hobbies;
        }
        if (isset($this->location)) {
            $this->location = clone $this->location;
        }
    }


}

