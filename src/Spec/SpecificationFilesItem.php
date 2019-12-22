<?php

namespace Helmich\Schema2Class\Spec;

class SpecificationFilesItem
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static array $schema = [
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
     * @var string
     */
    private string $input;

    /**
     * @var string
     */
    private string $className;

    /**
     * @var string
     */
    private string $targetDirectory;

    /**
     * @var string|null
     */
    private ?string $targetNamespace = null;

    /**
     * @param string $input
     * @param string $className
     * @param string $targetDirectory
     */
    public function __construct(string $input, string $className, string $targetDirectory)
    {
        $this->input = $input;
        $this->className = $className;
        $this->targetDirectory = $targetDirectory;
    }

    /**
     * @return string
     */
    public function getInput() : string
    {
        return $this->input;
    }

    /**
     * @return string
     */
    public function getClassName() : string
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getTargetDirectory() : string
    {
        return $this->targetDirectory;
    }

    /**
     * @return string|null
     */
    public function getTargetNamespace() : ?string
    {
        return isset($this->targetNamespace) ? $this->targetNamespace : null;
    }

    /**
     * @param string $input
     * @return self
     */
    public function withInput(string $input) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($input, static::$schema['properties']['input']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->input = $input;

        return $clone;
    }

    /**
     * @param string $className
     * @return self
     */
    public function withClassName(string $className) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($className, static::$schema['properties']['className']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->className = $className;

        return $clone;
    }

    /**
     * @param string $targetDirectory
     * @return self
     */
    public function withTargetDirectory(string $targetDirectory) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($targetDirectory, static::$schema['properties']['targetDirectory']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->targetDirectory = $targetDirectory;

        return $clone;
    }

    /**
     * @param string $targetNamespace
     * @return self
     */
    public function withTargetNamespace(string $targetNamespace) : self
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($targetNamespace, static::$schema['properties']['targetNamespace']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->targetNamespace = $targetNamespace;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutTargetNamespace() : self
    {
        $clone = clone $this;
        unset($clone->targetNamespace);

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array $input2 Input data
     * @return SpecificationFilesItem Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input2) : SpecificationFilesItem
    {
        static::validateInput($input2);

        $input = $input2['input'];
        $className = $input2['className'];
        $targetDirectory = $input2['targetDirectory'];
        $targetNamespace = null;
        if (isset($input2['targetNamespace'])) {
            $targetNamespace = $input2['targetNamespace'];
        }

        $obj = new static($input, $className, $targetDirectory);
        $obj->targetNamespace = $targetNamespace;
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
        $output['input'] = $this->input;
        $output['className'] = $this->className;
        $output['targetDirectory'] = $this->targetDirectory;
        if (isset($this->targetNamespace)) {
            $output['targetNamespace'] = $this->targetNamespace;
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
    }


}

