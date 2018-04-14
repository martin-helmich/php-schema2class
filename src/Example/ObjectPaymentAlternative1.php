<?php

namespace Helmich\JsonStructBuilder\Example;

class ObjectPaymentAlternative1
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array $schema
     */
    private static $schema = [
        'required' => [
            'type',
        ],
        'properties' => [
            'type' => [
                'type' => 'string',
                'enum' => [
                    'invoice',
                ],
            ],
        ],
    ];

    /**
     * @var string $type
     */
    private $type = null;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return self
     */
    public function withType($type)
    {
        $clone = clone $this;
        $clone->type = $type;

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array $input Input data
     * @return ObjectPaymentAlternative1 Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput(array $input)
    {
        static::validateInput($input);

        $obj = new static;
        $obj->type = $input['type'];

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

