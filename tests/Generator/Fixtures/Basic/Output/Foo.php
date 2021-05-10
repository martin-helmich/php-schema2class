<?php

declare(strict_types=1);

namespace Ns;

class Foo
{
    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static array $schema = [
        'properties' => [
            'foo' => [
                'type' => 'string',
            ],
            'foo_bar' => [
                'type' => 'string',
            ],
        ],
    ];

    /**
     * @var string|null
     */
    private ?string $foo = null;

    /**
     * @var string|null
     */
    private ?string $foo_bar = null;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @return string|null
     */
    public function getFoo() : ?string
    {
        return isset($this->foo) ? $this->foo : null;
    }

    /**
     * @return string|null
     */
    public function getFooBar() : ?string
    {
        return isset($this->foo_bar) ? $this->foo_bar : null;
    }

    /**
     * @param string $foo
     * @return self
     */
    public function withFoo(string $foo) : self
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
     * @param string $foo_bar
     * @return self
     */
    public function withFooBar(string $foo_bar) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($foo_bar, static::$schema['properties']['foo_bar']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->foo_bar = $foo_bar;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutFooBar() : self
    {
        $clone = clone $this;
        unset($clone->foo_bar);

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return Foo Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input) : Foo
    {
        static::validateInput($input);

        $foo = null;
        if (isset($input['foo'])) {
            $foo = $input['foo'];
        }
        $foo_bar = null;
        if (isset($input['foo_bar'])) {
            $foo_bar = $input['foo_bar'];
        }

        $obj = new self();
        $obj->foo = $foo;
        $obj->foo_bar = $foo_bar;
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
        if (isset($this->foo)) {
            $output['foo'] = $this->foo;
        }
        if (isset($this->foo_bar)) {
            $output['foo_bar'] = $this->foo_bar;
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
        $asObject = $validator::arrayToObjectRecursive($input);
        $validator->validate($asObject, static::$schema);

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

