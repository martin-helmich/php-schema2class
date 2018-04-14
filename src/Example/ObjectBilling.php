<?php

namespace Helmich\JsonStructBuilder\Example;

class ObjectBilling
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array $schema
     */
    private static $schema = [
        'required' => [
            'vatID',
        ],
        'properties' => [
            'vatID' => [
                'type' => 'string',
            ],
            'foo' => [
                'type' => 'int',
            ],
            'bar' => [
                'type' => 'string',
            ],
        ],
    ];

    /**
     * @var string $vatID
     */
    private $vatID = null;

    /**
     * @var int|null $foo
     */
    private $foo = null;

    /**
     * @var string|null $bar
     */
    private $bar = null;

    /**
     * @return string
     */
    public function getVatID()
    {
        return $this->vatID;
    }

    /**
     * @param string $vatID
     * @return self
     */
    public function withVatID($vatID)
    {
        $clone = clone $this;
        $clone->vatID = $vatID;

        return $clone;
    }

    /**
     * @return int|null
     */
    public function getFoo()
    {
        return $this->foo;
    }

    /**
     * @param int|null $foo
     * @return self
     */
    public function withFoo($foo)
    {
        $clone = clone $this;
        $clone->foo = $foo;

        return $clone;
    }

    /**
     * @return string|null
     */
    public function getBar()
    {
        return $this->bar;
    }

    /**
     * @param string|null $bar
     * @return self
     */
    public function withBar($bar)
    {
        $clone = clone $this;
        $clone->bar = $bar;

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return ObjectBilling Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input)
    {
        static::validateInput($input);

        $obj = new static;
        $obj->vatID = $input['vatID'];
        if (isset($input['foo'])) {
            $obj->foo = (int) $input['foo'];
        }
        if (isset($input['bar'])) {
            $obj->bar = $input['bar'];
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
    }


}

