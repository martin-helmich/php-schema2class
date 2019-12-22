<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;

abstract class AbstractPropertyInterface implements PropertyInterface
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
        return "\$$key = \${$inputVarName}['$key'];";
    }

    public function convertTypeToJSON(string $outputVarName = 'output'): string
    {
        $key = $this->key;
        return "\${$outputVarName}['$key'] = \$this->$key;";
    }

    protected function getOrNull(string $key)
    {
        return isset($this->schema[$key]) ? $this->schema[$key] : null;
    }

    public function generateSubTypes(SchemaToClass $generator): void
    {
    }

}
