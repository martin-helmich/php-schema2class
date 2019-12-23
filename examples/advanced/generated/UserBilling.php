<?php
declare(strict_types = 1);

namespace Example\Advanced;

class UserBilling
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = array(
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
            'foo' => array(
                'type' => 'int',
            ),
            'bar' => array(
                'type' => 'string',
            ),
        ),
    );

    /**
     * @var string
     */
    private $vatID = null;

    /**
     * @var int|null
     */
    private $creditLevel = null;

    /**
     * @var int|null
     */
    private $foo = null;

    /**
     * @var string|null
     */
    private $bar = null;

    /**
     * @param string $vatID
     */
    public function __construct(string $vatID)
    {
        $this->vatID = $vatID;
    }

    /**
     * @return string
     */
    public function getVatID() : string
    {
        return $this->vatID;
    }

    /**
     * @return int|null
     */
    public function getCreditLevel() : ?int
    {
        return $this->creditLevel;
    }

    /**
     * @return int|null
     */
    public function getFoo() : ?int
    {
        return $this->foo;
    }

    /**
     * @return string|null
     */
    public function getBar() : ?string
    {
        return $this->bar;
    }

    /**
     * @param string $vatID
     * @return self
     */
    public function withVatID(string $vatID) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($vatID, static::$schema['properties']['vatID']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->vatID = $vatID;

        return $clone;
    }

    /**
     * @param int $creditLevel
     * @return self
     */
    public function withCreditLevel(int $creditLevel) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($creditLevel, static::$schema['properties']['creditLevel']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->creditLevel = $creditLevel;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutCreditLevel() : self
    {
        $clone = clone $this;
        unset($clone->creditLevel);

        return $clone;
    }

    /**
     * @param int $foo
     * @return self
     */
    public function withFoo(int $foo) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($foo, static::$schema['properties']['foo']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->foo = $foo;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutFoo() : self
    {
        $clone = clone $this;
        unset($clone->foo);

        return $clone;
    }

    /**
     * @param string $bar
     * @return self
     */
    public function withBar(string $bar) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($bar, static::$schema['properties']['bar']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->bar = $bar;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutBar() : self
    {
        $clone = clone $this;
        unset($clone->bar);

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return UserBilling Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input) : UserBilling
    {
        static::validateInput($input);

        $vatID = $input['vatID'];
        $creditLevel = null;
        if (isset($input['creditLevel'])) {
            $creditLevel = (int) $input['creditLevel'];
        }
        $foo = null;
        if (isset($input['foo'])) {
            $foo = (int) $input['foo'];
        }
        $bar = null;
        if (isset($input['bar'])) {
            $bar = $input['bar'];
        }

        $obj = new static($vatID);
        $obj->creditLevel = $creditLevel;
        $obj->foo = $foo;
        $obj->bar = $bar;
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
        $output['vatID'] = $this->vatID;
        if (isset($this->creditLevel)) {
            $output['creditLevel'] = $this->creditLevel;
        }
        if (isset($this->foo)) {
            $output['foo'] = $this->foo;
        }
        if (isset($this->bar)) {
            $output['bar'] = $this->bar;
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

