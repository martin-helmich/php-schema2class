<?php
declare(strict_types=1);

namespace Helmich\Schema2Class\Spec;

class ValidatedSpecificationFilesItem
{
    private string $targetNamespace;
    private string $targetClass;
    private string $targetDirectory;

    /**
     * ValidatedSpecificationFilesItem constructor.
     * @param string $targetNamespace
     * @param string $targetClass
     * @param string $targetDirectory
     */
    public function __construct(string $targetNamespace, string $targetClass, string $targetDirectory)
    {
        $this->targetNamespace = $targetNamespace;
        $this->targetClass     = $targetClass;
        $this->targetDirectory = $targetDirectory;
    }

    public static function fromSpecificationFilesItem(SpecificationFilesItem $input, string $fallbackNamespace): ValidatedSpecificationFilesItem {
        return new ValidatedSpecificationFilesItem(
            $input->getTargetNamespace() ?? $fallbackNamespace,
            $input->getClassName(),
            $input->getTargetDirectory(),
        );
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
     * @return string
     */
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }

    public function withTargetClass(string $targetClass): self
    {
        $c              = clone $this;
        $c->targetClass = $targetClass;

        return $c;
    }

}