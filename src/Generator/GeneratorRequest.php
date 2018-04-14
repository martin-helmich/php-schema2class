<?php
namespace Helmich\JsonStructBuilder\Generator;


class GeneratorRequest
{
    public $schema;
    public $targetDirectory;
    public $targetNamespace;
    public $targetClass;
    public $php5 = false;
    public $noSetters = false;

    /**
     * GeneratorRequest constructor.
     * @param $schema
     * @param $targetDirectory
     * @param $targetNamespace
     * @param $targetClass
     */
    public function __construct($schema, $targetDirectory, $targetNamespace, $targetClass)
    {
        $this->schema = $schema;
        $this->targetDirectory = $targetDirectory;
        $this->targetNamespace = $targetNamespace;
        $this->targetClass = $targetClass;
    }

    public function withSchema(array $schema)
    {
        $clone = clone $this;
        $clone->schema = $schema;

        return $clone;
    }

    public function withClass($targetClass)
    {
        $clone = clone $this;
        $clone->targetClass = $targetClass;

        return $clone;
    }
}