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
    public $input = null;

    /**
     * @var string $className
     */
    public $className = null;

    /**
     * @var string $targetDirectory
     */
    public $targetDirectory = null;

    /**
     * @var string|null $targetNamespace
     */
    public $targetNamespace = null;

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


}

