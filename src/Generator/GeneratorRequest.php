<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator;

use Composer\Semver\Comparator;

class GeneratorRequest
{
    private array $schema;

    private string $targetDirectory;

    private string $targetNamespace;

    private string $targetClass;

    private string $targetPHPVersion;

    public function __construct(array $schema, string $targetDirectory, string $targetNamespace, string $targetClass, string $targetPHPVersion = "7.2")
    {
        $this->schema = $schema;
        $this->targetDirectory = $targetDirectory;
        $this->targetNamespace = $targetNamespace;
        $this->targetClass = $targetClass;
        $this->targetPHPVersion = self::semversifyVersionNumber($targetPHPVersion);
    }

    private static function semversifyVersionNumber(string $versionNumber): string {
        if (substr_count($versionNumber, '.') === 1) {
            return $versionNumber . ".0";
        }

        return $versionNumber;
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

    public function withPHPVersion(string $targetPHPVersion): self
    {
        $clone = clone $this;
        $clone->targetPHPVersion = self::semversifyVersionNumber($targetPHPVersion);

        return $clone;
    }

    public function getPHPTargetVersion(): string
    {
        return $this->targetPHPVersion;
    }

    /**
     * @param int $version
     * @return bool
     */
    public function isPhp(int $version): bool
    {
        switch ($version) {
            case 5:
                return Comparator::greaterThanOrEqualTo($this->targetPHPVersion, "5.6.0")
                    && Comparator::lessThan($this->targetPHPVersion, "6.0.0");
            case 7:
                return Comparator::greaterThanOrEqualTo($this->targetPHPVersion, "7.0.0");
            default:
                return false;
        }
    }

    public function isAtLeastPHP(string $version): bool
    {
        return Comparator::greaterThanOrEqualTo($this->targetPHPVersion, self::semversifyVersionNumber($version));
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
