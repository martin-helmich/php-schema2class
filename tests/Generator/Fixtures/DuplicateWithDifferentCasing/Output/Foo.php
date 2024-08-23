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
    private static array $schema = [
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
     * @param string $fooBar
     * @return self
     */
    public function withFooBar(string $fooBar) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($fooBar, static::$schema['properties']['fooBar']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->fooBar = $fooBar;

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

        $obj = new self($fooBar);
        $obj->foobar = $foobar;
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