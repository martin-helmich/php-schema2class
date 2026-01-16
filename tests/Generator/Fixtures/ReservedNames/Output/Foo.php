<?php

declare(strict_types=1);

namespace Ns\ReservedNames;

class Foo
{
    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static array $internalValidationSchema = [
        'required' => [
            'schema',
        ],
        'properties' => [
            'foo' => [
                'type' => 'string',
            ],
            'schema' => [
                'type' => 'string',
            ],
        ],
    ];

    /**
     * @var string|null
     */
    private ?string $foo = null;

    /**
     * @var string
     */
    private string $schema;

    /**
     * @param string $schema
     */
    public function __construct(string $schema)
    {
        $this->schema = $schema;
    }

    /**
     * @return string|null
     */
    public function getFoo(): ?string
    {
        return $this->foo ?? null;
    }

    /**
     * @return string
     */
    public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * @param string $foo
     * @return self
     */
    public function withFoo(string $foo): self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($foo, self::$internalValidationSchema['properties']['foo']);
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
    public function withoutFoo(): self
    {
        $clone = clone $this;
        unset($clone->foo);

        return $clone;
    }

    /**
     * @param string $schema
     * @return self
     */
    public function withSchema(string $schema): self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($schema, self::$internalValidationSchema['properties']['schema']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->schema = $schema;

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

        $foo = null;
        if (isset($input->{'foo'})) {
            $foo = $input->{'foo'};
        }
        $schema = $input->{'schema'};

        $obj = new self($schema);
        $obj->foo = $foo;
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
        if (isset($this->foo)) {
            $output['foo'] = $this->foo;
        }
        $output['schema'] = $this->schema;

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