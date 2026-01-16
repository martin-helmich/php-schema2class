<?php

declare(strict_types=1);

namespace Ns\UnionCollapsing;

class Foo
{
    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static array $internalValidationSchema = [
        'required' => [
            'foo',
        ],
        'properties' => [
            'foo' => [
                'oneOf' => [
                    [
                        'type' => 'string',
                        'format' => 'uuid',
                    ],
                    [
                        'type' => 'string',
                        'maxLength' => 0,
                    ],
                ],
            ],
        ],
    ];

    /**
     * @var string
     */
    private string $foo;

    /**
     * @param string $foo
     */
    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }

    /**
     * @return string
     */
    public function getFoo(): string
    {
        return $this->foo;
    }

    /**
     * @param string $foo
     * @return self
     */
    public function withFoo(string $foo): self
    {
        $clone = clone $this;
        $clone->foo = $foo;

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
    public static function buildFromInput(array|object $input, bool $validate = true): Foo
    {
        $input = is_array($input) ? \JsonSchema\Validator::arrayToObjectRecursive($input) : $input;
        if ($validate) {
            static::validateInput($input);
        }

        $foo = match (true) {
            is_string($input->{'foo'}) => $input->{'foo'},
            default => throw new \InvalidArgumentException("could not build property 'foo' from JSON"),
        };

        $obj = new self($foo);

        return $obj;
    }

    /**
     * Converts this object back to a simple array that can be JSON-serialized
     *
     * @return array Converted array
     */
    public function toJson(): array
    {
        $output = [];
        $output['foo'] = match (true) {
            is_string($this->foo) => $this->foo,
        };

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
    public static function validateInput(array|object $input, bool $return = false): bool
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
        $this->foo = match (true) {
            is_string($this->foo) => $this->foo,
        };
    }
}