<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;

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
        $this->capitalizedName = strtoupper($this->key[0]) . substr($this->key, 1);
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

    public function cloneProperty()
    {
        return null;
    }

    public function convertJSONToType(string $inputVarName = 'input'): string
    {
        $key = $this->key;
        $keyS = var_export($key, true);
        $map = $this->generateInputMappingExpr("\${$inputVarName}[{$keyS}]");
        return "\$$key = {$map};";
    }

    public function convertTypeToJSON(string $outputVarName = 'output'): string
    {
        $key = $this->key;
        $keyStr = var_export($key, true);
        $map = $this->generateOutputMappingExpr("\$this->{$key}");
        return "\${$outputVarName}[{$keyStr}] = {$map};";
    }

    protected function getOrNull(string $key)
    {
        return isset($this->schema[$key]) ? $this->schema[$key] : null;
    }

    public function generateInputAssertionExpr(string $expr): string
    {
        return $this->generateTypeAssertionExpr($expr);
    }

    public function generateInputMappingExpr(string $expr): string
    {
        return $expr;
    }

    public function generateOutputMappingExpr(string $expr): string
    {
        return $expr;
    }

    public function generateSubTypes(SchemaToClass $generator): void
    {
    }

}
