<?php

declare(strict_types=1);

namespace Helmich\Schema2Class\Spec;

class Specification
{
    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static array $schema = [
        'required' => [
            'files',
        ],
        'properties' => [
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
            ],
            'files' => [
                'type' => 'array',
                'items' => [
                    'required' => [
                        'input',
                        'className',
                        'targetDirectory',
                    ],
                    'properties' => [
                        'input' => [
                            'type' => 'string',
                        ],
                        'className' => [
                            'type' => 'string',
                        ],
                        'targetDirectory' => [
                            'type' => 'string',
                        ],
                        'targetNamespace' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
            'options' => [
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
            ],
        ],
    ];

    /**
     * @var int|string|null
     */
    private int|string|null $targetPHPVersion = null;

    /**
     * @var SpecificationFilesItem[]
     */
    private array $files;

    /**
     * @var SpecificationOptions|null
     */
    private ?SpecificationOptions $options = null;

    /**
     * @param SpecificationFilesItem[] $files
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    /**
     * @return int|string|null
     */
    public function getTargetPHPVersion() : int|string|null
    {
        return $this->targetPHPVersion;
    }

    /**
     * @return SpecificationFilesItem[]
     */
    public function getFiles() : array
    {
        return $this->files;
    }

    /**
     * @return SpecificationOptions|null
     */
    public function getOptions() : ?SpecificationOptions
    {
        return $this->options ?? null;
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
        unset($clone->targetPHPVersion);

        return $clone;
    }

    /**
     * @param SpecificationFilesItem[] $files
     * @return self
     */
    public function withFiles(array $files) : self
    {
        $clone = clone $this;
        $clone->files = $files;

        return $clone;
    }

    /**
     * @param SpecificationOptions $options
     * @return self
     */
    public function withOptions(SpecificationOptions $options) : self
    {
        $clone = clone $this;
        $clone->options = $options;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutOptions() : self
    {
        $clone = clone $this;
        unset($clone->options);

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array|object $input Input data
     * @param bool $validate Set this to false to skip validation; use at own risk
     * @return Specification Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array|object $input, bool $validate = true) : Specification
    {
        $input = is_array($input) ? \JsonSchema\Validator::arrayToObjectRecursive($input) : $input;
        if ($validate) {
            static::validateInput($input);
        }

        $targetPHPVersion = null;
        if (isset($input->{'targetPHPVersion'})) {
            $targetPHPVersion = match (true) {
                is_int($input->{'targetPHPVersion'}), is_string($input->{'targetPHPVersion'}) => $input->{'targetPHPVersion'},
                default => throw new \InvalidArgumentException("could not build property 'targetPHPVersion' from JSON"),
            };
        }
        $files = array_map(fn (array|object $i): SpecificationFilesItem => SpecificationFilesItem::buildFromInput($i, validate: $validate), $input->{'files'});
        $options = null;
        if (isset($input->{'options'})) {
            $options = SpecificationOptions::buildFromInput($input->{'options'}, validate: $validate);
        }

        $obj = new self($files);
        $obj->targetPHPVersion = $targetPHPVersion;
        $obj->options = $options;
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
        if (isset($this->targetPHPVersion)) {
            $output['targetPHPVersion'] = match (true) {
                is_int($this->targetPHPVersion), is_string($this->targetPHPVersion) => $this->targetPHPVersion,
            };
        }
        $output['files'] = array_map(fn (SpecificationFilesItem $i) => $i->toJson(), $this->files);
        if (isset($this->options)) {
            $output['options'] = ($this->options)->toJson();
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
        $this->files = array_map(fn (SpecificationFilesItem $i) => clone $i, $this->files);
        if (isset($this->options)) {
            $this->options = clone $this->options;
        }
    }
}

