<?php
declare(strict_types = 1);

namespace Helmich\Schema2Class\Spec;

class Specification
{

    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = array(
        'required' => array(
            'files',
        ),
        'properties' => array(
            'targetPHPVersion' => array(
                'type' => 'integer',
                'enum' => array(
                    5,
                    7,
                ),
                'default' => 7,
            ),
            'files' => array(
                'type' => 'array',
                'items' => array(
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
                ),
            ),
        ),
    );

    /**
     * @var int|null
     */
    private $targetPHPVersion = 7;

    /**
     * @var SpecificationFilesItem[]
     */
    private $files = null;

    /**
     * @param SpecificationFilesItem[] $files
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    /**
     * @return int|null
     */
    public function getTargetPHPVersion()
    {
        return $this->targetPHPVersion;
    }

    /**
     * @return SpecificationFilesItem[]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param int $targetPHPVersion
     * @return self
     */
    public function withTargetPHPVersion($targetPHPVersion)
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($targetPHPVersion, static::$schema['properties']['targetPHPVersion']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->targetPHPVersion = $targetPHPVersion;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutTargetPHPVersion()
    {
        $clone = clone $this;
        unset($clone->targetPHPVersion);

        return $clone;
    }

    /**
     * @param SpecificationFilesItem[] $files
     * @return self
     */
    public function withFiles(array $files)
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

        $targetPHPVersion = null;
        if (isset($input['targetPHPVersion'])) {
            $targetPHPVersion = (int) $input['targetPHPVersion'];
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
    public function toJson()
    {
        $output = [];
        if (isset($this->targetPHPVersion)) {
            $output['targetPHPVersion'] = $this->targetPHPVersion;
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
        $this->files = array_map(function(SpecificationFilesItem $i) { return clone $i; }, $this->files);
    }


}

