<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;
use Helmich\Schema2Class\Util\StringUtils;

abstract class AbstractProperty implements PropertyInterface
{

    protected string $key;

    protected array $schema;

    protected string $capitalizedName;

    protected GeneratorRequest $generatorRequest;

    public function __construct(string $key, array $schema, GeneratorRequest $generatorRequest)
    {
        $this->key = $key;
        $this->schema = $schema;
        $this->capitalizedName = StringUtils::capitalizeName($this->key);
        $this->generatorRequest = $generatorRequest;
    }

    public function isComplex(): bool
    {
        return false;
    }

    public function schema(): array
    {
        return $this->schema;
    }

    public function key(): string
    {
        return $this->key;
    }

    /**
     * @return string|null
     */
    public function cloneProperty(): ?string
    {
        $key = $this->key;
        $expr = "\$this->{$key}";
        $exprClone = $this->generateCloneExpr($expr);

        if ($expr !== $exprClone) {
            return "\$this->$key = {$exprClone};";
        }

        return null;
    }

    public function convertJSONToType(string $inputVarName = 'input', bool $object = false): string
    {
        $key = $this->key;
        $keyS = var_export($key, true);

        if ($object) {
            $map = $this->generateInputMappingExpr("\${$inputVarName}->{{$keyS}}");
        } else {
            $map = $this->generateInputMappingExpr("\${$inputVarName}[{$keyS}]");
        }

        return "\$$key = {$map};";
    }

    public function convertTypeToJSON(string $outputVarName = 'output'): string
    {
        $key = $this->key;
        $keyStr = var_export($key, true);
        $map = $this->generateOutputMappingExpr("\$this->{$key}");
        return "\${$outputVarName}[{$keyStr}] = {$map};";
    }

    public function generateInputAssertionExpr(string $expr): string
    {
        return $this->generateTypeAssertionExpr($expr);
    }

    public function generateInputMappingExpr(string $expr, bool $asserted = false): string
    {
        return $expr;
    }

    public function generateOutputMappingExpr(string $expr): string
    {
        return $expr;
    }

    public function generateCloneExpr(string $expr): string
    {
        return $expr;
    }

    public function generateSubTypes(SchemaToClass $generator): void
    {
    }

}
