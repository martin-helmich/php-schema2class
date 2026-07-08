<?php

declare(strict_types=1);

namespace Ns\PropsWithAdditionalProps;

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
            'reason',
        ],
        'properties' => [
            'reason' => [
                'type' => 'string',
            ],
        ],
        'additionalProperties' => [
            'type' => 'string',
        ],
    ];

    /**
     * @var string
     */
    private string $reason;

    /**
     * Properties from the input that are not explicitly declared in the schema
     *
     * @var string[]
     */
    private array $additionalProperties = [];

    /**
     * @param string $reason
     */
    public function __construct(string $reason)
    {
        $this->reason = $reason;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     * @return self
     */
    public function withReason(string $reason): self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($reason, self::$internalValidationSchema['properties']['reason']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->reason = $reason;

        return $clone;
    }

    /**
     * @return string[]
     */
    public function getAdditionalProperties(): array
    {
        return $this->additionalProperties;
    }

    /**
     * @param string[] $additionalProperties
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

        $reason = $input->{'reason'};

        $obj = new self($reason);

        foreach (get_object_vars($input) as $key => $value) {
            if (in_array($key, ['reason'], true)) {
                continue;
            }
            $obj->additionalProperties[$key] = $value;
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
            $output[$key] = $value;
        }
        $output['reason'] = $this->reason;

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