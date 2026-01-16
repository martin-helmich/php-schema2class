<?php

declare(strict_types=1);

namespace Ns\SpecialCharacterNames;

class Foo
{
    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static array $internalValidationSchema = [
        'required' => [
            'foo:bar',
        ],
        'properties' => [
            'foo:bar' => [
                'type' => 'string',
            ],
        ],
    ];

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
    public function getFooBar(): string
    {
        return $this->fooBar;
    }

    /**
     * @param string $fooBar
     * @return self
     */
    public function withFooBar(string $fooBar): self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($fooBar, self::$internalValidationSchema['properties']['foo:bar']);
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
    public static function buildFromInput(array|object $input, bool $validate = true): Foo
    {
        $input = is_array($input) ? \JsonSchema\Validator::arrayToObjectRecursive($input) : $input;
        if ($validate) {
            static::validateInput($input);
        }

        $fooBar = $input->{'foo:bar'};

        $obj = new self($fooBar);

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
        $output['foo:bar'] = $this->fooBar;

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
    }
}