<?php

namespace Helmich\JsonStructBuilder\Spec;

class Specification
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array $schema
     */
    private static $schema = [
        'required' => [
            'files',
        ],
        'properties' => [
            'targetPHPVersion' => [
                'type' => 'integer',
                'enum' => [
                    5,
                    7,
                ],
                'default' => 7,
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
     * @var int $targetPHPVersion
     */
    private $targetPHPVersion = 7;

    /**
     * @var SpecificationFilesItem[] $files
     */
    private $files = null;

    /**
     * @return int
     */
    public function getTargetPHPVersion()
    {
        return $this->targetPHPVersion;
    }

    /**
     * @param int $targetPHPVersion
     * @return self
     */
    public function withTargetPHPVersion($targetPHPVersion)
    {
        $clone = clone $this;
        $clone->targetPHPVersion = $targetPHPVersion;

        return $clone;
    }

    /**
     * @return SpecificationFilesItem[]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param SpecificationFilesItem[] $files
     * @return self
     */
    public function withFiles($files)
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
    public static function buildFromInput(array $input)
    {
        static::validateInput($input);

        $obj = new static;
        if (isset($input['targetPHPVersion'])) {
            $obj->targetPHPVersion = (int) $input['targetPHPVersion'];
        }
        $obj->files = array_map(function($i) { return SpecificationFilesItem::buildFromInput($i); }, $input["files"]);

        return $obj;
    }

    /**
     * Validates an input array
     *
     * @param array $input Input data
     * @param bool $return Return instead of throwing errors
     * @return bool Validation result
     * @throws \InvalidArgumentException
     */
    public static function validateInput($input, $return = false)
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($input, static::$schema);

        if (!$validator->isValid() && !$return) {
            $errors = array_map(function($e) {
                return $e["property"] . ": " . $e["message"];
            }, $validator->getErrors());
            throw new \InvalidArgumentException(join(", ", $errors));
        }

        return $validator->isValid();
    }

    public function __clone()
    {
        $this->files = array_map(function($i) { return clone $i; }, $this->files);
    }


}

