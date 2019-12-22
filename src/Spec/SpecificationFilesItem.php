<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Spec;

class SpecificationFilesItem
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = array(
        'required' => array(
            'input',
            'className',
            'targetDirectory',
        ),
        'properties' => array(
            'input' => array(
                'type' => 'string',
            ),
            'className' => array(
                'type' => 'string',
            ),
            'targetDirectory' => array(
                'type' => 'string',
            ),
            'targetNamespace' => array(
                'type' => 'string',
            ),
        ),
    );

    /**
     * @var string
     */
    private $input = null;

    /**
     * @var string
     */
    private $className = null;

    /**
     * @var string
     */
    private $targetDirectory = null;

    /**
     * @var string|null
     */
    private $targetNamespace = null;

    /**
     * @param string $input
     * @param string $className
     * @param string $targetDirectory
     */
    public function __construct($input, $className, $targetDirectory)
    {
        $this->input = $input;
        $this->className = $className;
        $this->targetDirectory = $targetDirectory;
    }

    /**
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    /**
     * @return string|null
     */
    public function getTargetNamespace()
    {
        return $this->targetNamespace;
    }

    /**
     * @param string $input
     * @return self
     */
    public function withInput($input)
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
    public function withClassName($className)
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
    public function withTargetDirectory($targetDirectory)
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
    public function withTargetNamespace($targetNamespace)
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
    public function withoutTargetNamespace()
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
    public static function buildFromInput(array $input2)
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
    public function toJson()
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

