<?php

declare(strict_types=1);

namespace Ns\PropsWithAdditionalObjectProps;

class Foo
{
    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static array $internalValidationSchema = [
        'type' => 'object',
        'required' => [
            'name',
        ],
        'properties' => [
            'name' => [
                'type' => 'string',
            ],
        ],
        'additionalProperties' => [
            'type' => 'object',
            'required' => [
                'value',
            ],
            'properties' => [
                'value' => [
                    'type' => 'string',
                ],
            ],
        ],
    ];

    /**
     * @var string
     */
    private string $name;

    /**
     * Properties from the input that are not explicitly declared in the schema
     *
     * @var FooAdditionalPropertiesItem[]
     */
    private array $additionalProperties = [];

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function withName(string $name): self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($name, self::$internalValidationSchema['properties']['name']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    /**
     * @return FooAdditionalPropertiesItem[]
     */
    public function getAdditionalProperties(): array
    {
        return $this->additionalProperties;
    }

    /**
     * @param FooAdditionalPropertiesItem[] $additionalProperties
     * @return self
     */
    public function withAdditionalProperties(array $additionalProperties): self
    {
        $clone = clone $this;
        $clone->additionalProperties = $additionalProperties;

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

        $name = $input->{'name'};

        $obj = new self($name);

        foreach (get_object_vars($input) as $key => $value) {
            if (in_array($key, ['name'], true)) {
                continue;
            }
            $obj->additionalProperties[$key] = FooAdditionalPropertiesItem::buildFromInput($value, validate: $validate);
        }
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
        foreach ($this->additionalProperties as $key => $value) {
            $output[$key] = ($value)->toJson();
        }
        $output['name'] = $this->name;

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
        $this->additionalProperties = array_map(fn ($value) => clone $value, $this->additionalProperties);
    }
}