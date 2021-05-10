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
                'type' => 'bool',
                'default' => false,
            ],
            'targetPHPVersion' => [
                'oneOf' => [
                    [
                        'type' => 'integer',
                        'enum' => [
                            5,
                            7,
                        ],
                    ],
                    [
                        'type' => 'string',
                    ],
                ],
                'default' => '7.4.0',
            ],
        ],
    ];

    /**
     * @var bool
     */
    private bool $disableStrictTypes = false;

    /**
     * @var int|string
     */
    private $targetPHPVersion = '7.4.0';

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
     * @return int|string
     */
    public function getTargetPHPVersion()
    {
        return $this->targetPHPVersion;
    }

    /**
     * @param bool $disableStrictTypes
     * @return self
     */
    public function withDisableStrictTypes(bool $disableStrictTypes) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($disableStrictTypes, static::$schema['properties']['disableStrictTypes']);
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
        unset($clone->disableStrictTypes);

        return $clone;
    }

    /**
     * @param int|string $targetPHPVersion
     * @return self
     */
    public function withTargetPHPVersion($targetPHPVersion) : self
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
        unset($clone->targetPHPVersion);

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return SpecificationOptions Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input) : SpecificationOptions
    {
        static::validateInput($input);

        $disableStrictTypes = false;
        if (isset($input['disableStrictTypes'])) {
            $disableStrictTypes = (bool)($input['disableStrictTypes']);
        }
        $targetPHPVersion = '7.4.0';
        if (isset($input['targetPHPVersion'])) {
            $targetPHPVersion = $input['targetPHPVersion'];
        }

        $obj = new self();
        $obj->disableStrictTypes = $disableStrictTypes;
        $obj->targetPHPVersion = $targetPHPVersion;
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
        if (isset($this->targetPHPVersion)) {
            if ((is_int($this->targetPHPVersion)) || (is_string($this->targetPHPVersion))) {
                $output['targetPHPVersion'] = $this->targetPHPVersion;
            }
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
        if (isset($this->targetPHPVersion)) {
            $this->targetPHPVersion = (is_string($this->targetPHPVersion)) ? ($this->targetPHPVersion) : ((is_int($this->targetPHPVersion)) ? ($this->targetPHPVersion) : ($this->targetPHPVersion));
        }
    }
}

