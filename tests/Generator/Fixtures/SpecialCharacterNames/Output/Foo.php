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
            'baz.qux' => [
                'type' => 'boolean',
            ],
        ],
    ];

    /**
     * @var string
     */
    private string $fooBar;

    /**
     * @var bool|null
     */
    private ?bool $bazQux = null;

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
     * @return bool|null
     */
    public function getBazQux(): ?bool
    {
        return $this->bazQux ?? null;
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
     * @param bool $bazQux
     * @return self
     */
    public function withBazQux(bool $bazQux): self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($bazQux, self::$internalValidationSchema['properties']['baz.qux']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->bazQux = $bazQux;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutBazQux(): self
    {
        $clone = clone $this;
        unset($clone->bazQux);

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
        $bazQux = null;
        if (isset($input->{'baz.qux'})) {
            $bazQux = (bool)($input->{'baz.qux'});
        }

        $obj = new self($fooBar);
        $obj->bazQux = $bazQux;
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
        if (isset($this->bazQux)) {
            $output['baz.qux'] = $this->bazQux;
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