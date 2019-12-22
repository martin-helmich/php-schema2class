<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator;

class GeneratorRequest
{
    /** @var array */
    private $schema;

    /** @var string */
    private $targetDirectory;

    /** @var string */
    private $targetNamespace;

    /** @var string */
    private $targetClass;

    /** @var bool */
    //@todo Refactor and make private
    public $php5 = false;

    /**
     * GeneratorRequest constructor.
     * @param array $schema
     * @param string $targetDirectory
     * @param string $targetNamespace
     * @param string $targetClass
     */
    public function __construct(array $schema, string $targetDirectory, string $targetNamespace, string $targetClass)
    {
        $this->schema = $schema;
        $this->targetDirectory = $targetDirectory;
        $this->targetNamespace = $targetNamespace;
        $this->targetClass = $targetClass;
    }

    public function withSchema(array $schema): self
    {
        $clone = clone $this;
        $clone->schema = $schema;

        return $clone;
    }

    public function withClass($targetClass): self
    {
        $clone = clone $this;
        $clone->targetClass = $targetClass;

        return $clone;
    }

    /**
     * @return int
     */
    public function getPhpTargetVersion(): int
    {
        return $this->php5 ? 5 : 7;
    }

    /**
     * @param int $version
     * @return bool
     */
    public function isPhp(int $version): bool
    {
        return $this->getPhpTargetVersion() === $version;
    }

    /**
     * @return string
     */
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }

    /**
     * @return string
     */
    public function getTargetNamespace(): string
    {
        return $this->targetNamespace;
    }

    /**
     * @return string
     */
    public function getTargetClass(): string
    {
        return $this->targetClass;
    }

    /**
     * @return array
     */
    public function getSchema(): array
    {
        return $this->schema;
    }
}
