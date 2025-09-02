<?php

declare(strict_types=1);

namespace Ns\DuplicateWithDifferentCasing;

class Foo
{
    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static array $internalValidationSchema = [
        'required' => [
            'fooBar',
        ],
        'properties' => [
            'foobar' => [
                'type' => 'string',
                'deprecated' => true,
            ],
            'fooBar' => [
                'type' => 'string',
            ],
            'bar' => [
                'type' => 'string',
                'deprecated' => true,
            ],
        ],
    ];

    /**
     * @var string|null
     * @deprecated
     */
    private ?string $foobar = null;

    /**
     * @var string
     */
    private string $fooBar;

    /**
     * @var string|null
     * @deprecated
     */
    private ?string $bar = null;

    /**
     * @param string $fooBar
     */
    public function __construct(string $fooBar)
    {
        $this->fooBar = $fooBar;
    }

    /**
     * @return string
     */
    public function getFooBar() : string
    {
        return $this->fooBar;
    }

    /**
     * @return string|null
     * @deprecated
     */
    public function getBar() : ?string
    {
        return $this->bar ?? null;
    }

    /**
     * @param string $fooBar
     * @return self
     */
    public function withFooBar(string $fooBar) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($fooBar, self::$internalValidationSchema['properties']['fooBar']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->fooBar = $fooBar;

        return $clone;
    }

    /**
     * @param string $bar
     * @return self
     * @deprecated
     */
    public function withBar(string $bar) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($bar, self::$internalValidationSchema['properties']['bar']);
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
     * @param array|object $input Input data
     * @param bool $validate Set this to false to skip validation; use at own risk
     * @return Foo Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array|object $input, bool $validate = true) : Foo
    {
        $input = is_array($input) ? \JsonSchema\Validator::arrayToObjectRecursive($input) : $input;
        if ($validate) {
            static::validateInput($input);
        }

        $foobar = null;
        if (isset($input->{'foobar'})) {
            $foobar = $input->{'foobar'};
        }
        $fooBar = $input->{'fooBar'};
        $bar = null;
        if (isset($input->{'bar'})) {
            $bar = $input->{'bar'};
        }

        $obj = new self($fooBar);
        $obj->foobar = $foobar;
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
        if (isset($this->foobar)) {
            $output['foobar'] = $this->foobar;
        }
        $output['fooBar'] = $this->fooBar;
        if (isset($this->bar)) {
            $output['bar'] = $this->bar;
        }

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
        $validator->validate($input, self::$internalValidationSchema);

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