<?php
declare(strict_types=1);

namespace Helmich\Schema2Class\Generator;

use Composer\Semver\Comparator;
use Helmich\Schema2Class\Spec\SpecificationOptions;
use Helmich\Schema2Class\Spec\ValidatedSpecificationFilesItem;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;

class GeneratorRequest
{
    private array $schema;
    private ValidatedSpecificationFilesItem $spec;
    private SpecificationOptions $opts;
    private ?ReferenceLookup $referenceLookup = null;

    /** @var PropertyGenerator[] */
    private array $additionalProperties = [];

    /** @var MethodGenerator[] */
    private array $additionalMethods = [];

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
        $clone->referenceLookup = $referenceLookup;

        return $clone;
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

        return $clone;
    }

    public function withPHPVersion(string $targetPHPVersion): self
    {
        $clone       = clone $this;
        $clone->opts = $this->opts->withTargetPHPVersion(self::semversifyVersionNumber($targetPHPVersion));

        return $clone;
    }

    public function withAdditionalProperty(PropertyGenerator $property): self
    {
        $clone = clone $this;
        $clone->additionalProperties = [...$clone->additionalProperties, $property];

        return $clone;
    }

    public function withAdditionalMethod(MethodGenerator $method): self
    {
        $clone = clone $this;
        $clone->additionalMethods = [...$clone->additionalMethods, $method];

        return $clone;
    }

    public function getTargetPHPVersion(): string
    {
        return (string)$this->opts->getTargetPHPVersion();
    }

    /**
     * @return PropertyGenerator[]
     */
    public function getAdditionalProperties(): array
    {
        return $this->additionalProperties;
    }

    /**
     * @return MethodGenerator[]
     */
    public function getAdditionalMethods(): array
    {
        return $this->additionalMethods;
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
        if ($this->referenceLookup === null) {
            return new ReferencedTypeUnknown();
        }

        return $this->referenceLookup->lookupReference($ref);
    }
}
