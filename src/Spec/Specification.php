<?php

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
                        ],
                        'default' => 7,
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
        ],
    ];

    /**
     * @var int|string|null
     */
    private $targetPHPVersion = null;

    /**
     * @var SpecificationFilesItem[]
     */
    private array $files;

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
    public function getTargetPHPVersion()
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
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return Specification Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input) : Specification
    {
        static::validateInput($input);

        $targetPHPVersion = null;
        if (isset($input['targetPHPVersion'])) {
            if ((is_int($input['targetPHPVersion']))) {
                $targetPHPVersion = (int)($input['targetPHPVersion']);
            } else {
                $targetPHPVersion = $input['targetPHPVersion'];
            }
        }
        $files = array_map(function($i) { return SpecificationFilesItem::buildFromInput($i); }, $input['files']);

        $obj = new static($files);
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
        if (isset($this->targetPHPVersion)) {
            if ((is_int($this->targetPHPVersion)) || (is_string($this->targetPHPVersion))) {
                $output['targetPHPVersion'] = $this->targetPHPVersion;
            }
        }
        $output['files'] = array_map(function(SpecificationFilesItem $i) { return $i->toJson(); }, $this->files);

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
            $this->targetPHPVersion = (is_string($this->targetPHPVersion)) ? ($this->targetPHPVersion) : ((is_int($this->targetPHPVersion)) ? ($this->targetPHPVersion) : (null));
        }
        $this->files = array_map(function(SpecificationFilesItem $i) { return clone $i; }, $this->files);;
    }


}

