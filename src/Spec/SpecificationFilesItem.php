<?php

namespace Helmich\JsonStructBuilder\Spec;

class SpecificationFilesItem
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array $schema
     */
    private static $schema = [
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
    ];

    /**
     * @var string $input
     */
    private $input = null;

    /**
     * @var string $className
     */
    private $className = null;

    /**
     * @var string $targetDirectory
     */
    private $targetDirectory = null;

    /**
     * @var string|null $targetNamespace
     */
    private $targetNamespace = null;

    /**
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param string $input
     * @return self
     */
    public function withInput($input)
    {
        $clone = clone $this;
        $clone->input = $input;

        return $clone;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     * @return self
     */
    public function withClassName($className)
    {
        $clone = clone $this;
        $clone->className = $className;

        return $clone;
    }

    /**
     * @return string
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    /**
     * @param string $targetDirectory
     * @return self
     */
    public function withTargetDirectory($targetDirectory)
    {
        $clone = clone $this;
        $clone->targetDirectory = $targetDirectory;

        return $clone;
    }

    /**
     * @return string|null
     */
    public function getTargetNamespace()
    {
        return $this->targetNamespace;
    }

    /**
     * @param string|null $targetNamespace
     * @return self
     */
    public function withTargetNamespace($targetNamespace)
    {
        $clone = clone $this;
        $clone->targetNamespace = $targetNamespace;

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return SpecificationFilesItem Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input)
    {
        static::validateInput($input);

        $obj = new static;
        $obj->input = $input['input'];
        $obj->className = $input['className'];
        $obj->targetDirectory = $input['targetDirectory'];
        if (isset($input['targetNamespace'])) {
            $obj->targetNamespace = $input['targetNamespace'];
        }

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
    }


}

