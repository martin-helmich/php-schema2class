<?php
declare(strict_types=1);

namespace Helmich\Schema2Class\Generator;

use Composer\Semver\Comparator;
use Helmich\Schema2Class\Generator\Hook\AddInterfaceHook;
use Helmich\Schema2Class\Generator\Hook\AddMethodHook;
use Helmich\Schema2Class\Generator\Hook\AddPropertyHook;
use Helmich\Schema2Class\Spec\SpecificationOptions;
use Helmich\Schema2Class\Spec\ValidatedSpecificationFilesItem;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;

class GeneratorRequest
{
    private array $schema;
    private ValidatedSpecificationFilesItem $spec;
    private SpecificationOptions $opts;
    /** @var array<class-string, ReferenceLookup> */
    private array $referenceLookup = [];

    use GeneratorHookRunner;

    public function __construct(array $schema, ValidatedSpecificationFilesItem $spec, SpecificationOptions $opts)
    {
        $opts = $opts->withTargetPHPVersion(self::semversifyVersionNumber($opts->getTargetPHPVersion()));

        $this->schema = $schema;
        $this->spec   = $spec;
        $this->opts   = $opts;
    }

    private static function semversifyVersionNumber(string|int $versionNumber): string
    {
        if (is_int($versionNumber)) {
            return $versionNumber . ".0.0";
        }

        if (substr_count($versionNumber, '.') === 1) {
            return $versionNumber . ".0";
        }

        return $versionNumber;
    }

    public function withReferenceLookup(ReferenceLookup $referenceLookup): self
    {
        $clone                  = clone $this;
        $clone->referenceLookup = [];
        $clone->referenceLookup[$referenceLookup::class] = $referenceLookup;

        return $clone;
    }

    public function withAdditionalReferenceLookup(ReferenceLookup $referenceLookup): self
    {
        $clone                  = clone $this;
        $clone->referenceLookup[$referenceLookup::class] = $referenceLookup;

        return $clone;
    }

    /**
     * @param class-string $referenceLookup
     */
    public function hasReferenceLookup(string $referenceLookup): bool
    {
        return isset($this->referenceLookup[$referenceLookup]);
    }

    public function withSchema(array $schema): self
    {
        $clone         = clone $this;
        $clone->schema = $schema;

        return $clone;
    }

    public function withClass(string $targetClass): self
    {
        $clone       = clone $this;
        $clone->spec = $this->spec->withTargetClass($targetClass);

        $clone->clearNonPropagatingHooks();

        return $clone;
    }

    public function withNamespace(string $targetNamespace): self
    {
        $clone       = clone $this;
        $clone->spec = $this->spec->withTargetNamespace($targetNamespace);

        $clone->clearNonPropagatingHooks();

        return $clone;
    }

    public function withDirectory(string $targetDirectory): self
    {
        $clone       = clone $this;
        $clone->spec = $this->spec->withTargetDirectory($targetDirectory);

        $clone->clearNonPropagatingHooks();

        return $clone;
    }

    public function withPHPVersion(string $targetPHPVersion): self
    {
        $clone       = clone $this;
        $clone->opts = $this->opts->withTargetPHPVersion(self::semversifyVersionNumber($targetPHPVersion));

        return $clone;
    }

    /**
     * Adds a property to generated classes.
     *
     * @param PropertyGenerator $property The property to add to generated classes.
     * @param bool $propagateToSubObjects Controls if the property should be added to sub-objects.
     * @return self
     */
    public function withAdditionalProperty(PropertyGenerator $property, bool $propagateToSubObjects = false): self
    {
        return $this->withHook(new AddPropertyHook($property), $propagateToSubObjects);
    }

    /**
     * Adds a method to generated classes.
     *
     * @param MethodGenerator $method The method to add to generated classes.
     * @param bool $propagateToSubObjects Controls if the method should be added to sub-objects.
     * @return self
     */
    public function withAdditionalMethod(MethodGenerator $method, bool $propagateToSubObjects = false): self
    {
        return $this->withHook(new AddMethodHook($method), $propagateToSubObjects);
    }

    /**
     * Adds an "implements" clause to generated classes.
     *
     * @psalm-param class-string $interface
     * @param string $interface The interface to add to generated classes.
     * @param bool $propagateToSubObjects Controls if the interface should be added to sub-objects.
     * @return self
     */
    public function withAdditionalInterface(string $interface, bool $propagateToSubObjects = false): self
    {
        return $this->withHook(new AddInterfaceHook($interface), $propagateToSubObjects);
    }

    public function getTargetPHPVersion(): string
    {
        return (string)$this->opts->getTargetPHPVersion();
    }

    /**
     * @param int $version
     * @return bool
     * @deprecated Use `isAtLeastPHP` instead
     */
    public function isPhp(int $version): bool
    {
        $target = $this->getTargetPHPVersion();
        switch ($version) {
            case 5:
                return Comparator::greaterThanOrEqualTo($target, "5.6.0")
                    && Comparator::lessThan($target, "6.0.0");
            case 7:
                return Comparator::greaterThanOrEqualTo($target, "7.0.0");
            default:
                return false;
        }
    }

    public function isAtLeastPHP(string $version): bool
    {
        return Comparator::greaterThanOrEqualTo($this->getTargetPHPVersion(), self::semversifyVersionNumber($version));
    }

    /**
     * @return string
     */
    public function getTargetDirectory(): string
    {
        return $this->spec->getTargetDirectory();
    }

    /**
     * @return string
     */
    public function getTargetNamespace(): string
    {
        return $this->spec->getTargetNamespace();
    }

    /**
     * @return string
     */
    public function getTargetClass(): string
    {
        return $this->spec->getTargetClass();
    }

    /**
     * @return array
     */
    public function getSchema(): array
    {
        return $this->schema;
    }

    public function getOptions(): SpecificationOptions
    {
        return $this->opts;
    }

    public function lookupReference(string $ref): ReferencedType
    {
        if (empty($this->referenceLookup)) {
            return new ReferencedTypeUnknown();
        }

        foreach ($this->referenceLookup as $referenceLookup) {
            $reference = $referenceLookup->lookupReference($ref);
            if (!$reference instanceof ReferencedTypeUnknown) {
                return $reference;
            }
        }

        return new ReferencedTypeUnknown();
    }

    public function lookupSchema(string $ref): array
    {
        if (empty($this->referenceLookup)) {
            return [];
        }

        foreach ($this->referenceLookup as $referenceLookup) {
            $schema = $referenceLookup->lookupSchema($ref);
            if (!empty($schema)) {
                return $schema;
            }
        }

        return [];
    }
}
