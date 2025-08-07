<?php

declare(strict_types=1);

namespace Helmich\Schema2Class\Spec;

class SpecificationOptions
{
    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static array $schema = [
        'properties' => [
            'disableStrictTypes' => [
                'type' => 'boolean',
                'default' => false,
            ],
            'treatValuesWithDefaultAsOptional' => [
                'type' => 'boolean',
                'default' => false,
            ],
            'inlineAllofReferences' => [
                'type' => 'boolean',
                'default' => false,
            ],
            'targetPHPVersion' => [
                'oneOf' => [
                    [
                        'type' => 'integer',
                        'enum' => [
                            5,
                            7,
                            8,
                        ],
                    ],
                    [
                        'type' => 'string',
                    ],
                ],
                'default' => '8.2.0',
            ],
            'newValidatorClassExpr' => [
                'type' => 'string',
                'description' => 'The expression to use to create a new instance of the validator class.
This is useful if you want to use a custom validator class.
',
                'default' => 'new \\JsonSchema\\Validator()',
            ],
        ],
    ];

    /**
     * @var bool
     */
    private bool $disableStrictTypes = false;

    /**
     * @var bool
     */
    private bool $treatValuesWithDefaultAsOptional = false;

    /**
     * @var bool
     */
    private bool $inlineAllofReferences = false;

    /**
     * @var int|string
     */
    private int|string $targetPHPVersion = '8.2.0';

    /**
     * The expression to use to create a new instance of the validator class.
     * This is useful if you want to use a custom validator class.
     *
     *
     * @var string
     */
    private string $newValidatorClassExpr = 'new \\JsonSchema\\Validator()';

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @return bool
     */
    public function getDisableStrictTypes() : bool
    {
        return $this->disableStrictTypes;
    }

    /**
     * @return bool
     */
    public function getTreatValuesWithDefaultAsOptional() : bool
    {
        return $this->treatValuesWithDefaultAsOptional;
    }

    /**
     * @return bool
     */
    public function getInlineAllofReferences() : bool
    {
        return $this->inlineAllofReferences;
    }

    /**
     * @return int|string
     */
    public function getTargetPHPVersion() : int|string
    {
        return $this->targetPHPVersion;
    }

    /**
     * @return string
     */
    public function getNewValidatorClassExpr() : string
    {
        return $this->newValidatorClassExpr;
    }

    /**
     * @param bool $disableStrictTypes
     * @return self
     */
    public function withDisableStrictTypes(bool $disableStrictTypes) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($disableStrictTypes, self::$schema['properties']['disableStrictTypes']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->disableStrictTypes = $disableStrictTypes;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutDisableStrictTypes() : self
    {
        $clone = clone $this;
        $clone->disableStrictTypes = false;

        return $clone;
    }

    /**
     * @param bool $treatValuesWithDefaultAsOptional
     * @return self
     */
    public function withTreatValuesWithDefaultAsOptional(bool $treatValuesWithDefaultAsOptional) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($treatValuesWithDefaultAsOptional, self::$schema['properties']['treatValuesWithDefaultAsOptional']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->treatValuesWithDefaultAsOptional = $treatValuesWithDefaultAsOptional;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutTreatValuesWithDefaultAsOptional() : self
    {
        $clone = clone $this;
        $clone->treatValuesWithDefaultAsOptional = false;

        return $clone;
    }

    /**
     * @param bool $inlineAllofReferences
     * @return self
     */
    public function withInlineAllofReferences(bool $inlineAllofReferences) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($inlineAllofReferences, self::$schema['properties']['inlineAllofReferences']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->inlineAllofReferences = $inlineAllofReferences;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutInlineAllofReferences() : self
    {
        $clone = clone $this;
        $clone->inlineAllofReferences = false;

        return $clone;
    }

    /**
     * @param int|string $targetPHPVersion
     * @return self
     */
    public function withTargetPHPVersion(int|string $targetPHPVersion) : self
    {
        $clone = clone $this;
        $clone->targetPHPVersion = $targetPHPVersion;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutTargetPHPVersion() : self
    {
        $clone = clone $this;
        $clone->targetPHPVersion = '8.2.0';

        return $clone;
    }

    /**
     * @param string $newValidatorClassExpr
     * @return self
     */
    public function withNewValidatorClassExpr(string $newValidatorClassExpr) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($newValidatorClassExpr, self::$schema['properties']['newValidatorClassExpr']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->newValidatorClassExpr = $newValidatorClassExpr;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutNewValidatorClassExpr() : self
    {
        $clone = clone $this;
        $clone->newValidatorClassExpr = 'new \\JsonSchema\\Validator()';

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array|object $input Input data
     * @param bool $validate Set this to false to skip validation; use at own risk
     * @return SpecificationOptions Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array|object $input, bool $validate = true) : SpecificationOptions
    {
        $input = is_array($input) ? \JsonSchema\Validator::arrayToObjectRecursive($input) : $input;
        if ($validate) {
            static::validateInput($input);
        }

        $disableStrictTypes = false;
        if (isset($input->{'disableStrictTypes'})) {
            $disableStrictTypes = (bool)($input->{'disableStrictTypes'});
        }
        $treatValuesWithDefaultAsOptional = false;
        if (isset($input->{'treatValuesWithDefaultAsOptional'})) {
            $treatValuesWithDefaultAsOptional = (bool)($input->{'treatValuesWithDefaultAsOptional'});
        }
        $inlineAllofReferences = false;
        if (isset($input->{'inlineAllofReferences'})) {
            $inlineAllofReferences = (bool)($input->{'inlineAllofReferences'});
        }
        $targetPHPVersion = '8.2.0';
        if (isset($input->{'targetPHPVersion'})) {
            $targetPHPVersion = match (true) {
                is_int($input->{'targetPHPVersion'}), is_string($input->{'targetPHPVersion'}) => $input->{'targetPHPVersion'},
                default => throw new \InvalidArgumentException("could not build property 'targetPHPVersion' from JSON"),
            };
        }
        $newValidatorClassExpr = 'new \\JsonSchema\\Validator()';
        if (isset($input->{'newValidatorClassExpr'})) {
            $newValidatorClassExpr = $input->{'newValidatorClassExpr'};
        }

        $obj = new self();
        $obj->disableStrictTypes = $disableStrictTypes;
        $obj->treatValuesWithDefaultAsOptional = $treatValuesWithDefaultAsOptional;
        $obj->inlineAllofReferences = $inlineAllofReferences;
        $obj->targetPHPVersion = $targetPHPVersion;
        $obj->newValidatorClassExpr = $newValidatorClassExpr;
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
        if (isset($this->disableStrictTypes)) {
            $output['disableStrictTypes'] = $this->disableStrictTypes;
        }
        if (isset($this->treatValuesWithDefaultAsOptional)) {
            $output['treatValuesWithDefaultAsOptional'] = $this->treatValuesWithDefaultAsOptional;
        }
        if (isset($this->inlineAllofReferences)) {
            $output['inlineAllofReferences'] = $this->inlineAllofReferences;
        }
        if (isset($this->targetPHPVersion)) {
            $output['targetPHPVersion'] = match (true) {
                is_int($this->targetPHPVersion), is_string($this->targetPHPVersion) => $this->targetPHPVersion,
            };
        }
        if (isset($this->newValidatorClassExpr)) {
            $output['newValidatorClassExpr'] = $this->newValidatorClassExpr;
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
        $validator->validate($input, self::$schema);

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
        if (isset($this->targetPHPVersion)) {
            $this->targetPHPVersion = match (true) {
                is_int($this->targetPHPVersion), is_string($this->targetPHPVersion) => $this->targetPHPVersion,
            };
        }
    }
}

