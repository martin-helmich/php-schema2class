<?php
namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Generator\GeneratorRequest;
use Helmich\Schema2Class\Generator\SchemaToClass;

abstract class AbstractPropertyInterface implements PropertyInterface
{

    /** @var string */
    protected $key;

    /** @var array */
    protected $schema;

    /** @var string */
    protected $capitalizedName;

    /** @var GeneratorRequest */
    protected $generatorRequest;

    public function __construct($key, array $schema, GeneratorRequest $generatorRequest)
    {
        $this->key = $key;
        $this->schema = $schema;
        $this->capitalizedName = strtoupper($this->key[0]) . substr($this->key, 1);
        $this->generatorRequest = $generatorRequest;
    }

    public function isComplex()
    {
        return false;
    }

    public function schema()
    {
        return $this->schema;
    }

    public function key()
    {
        return $this->key;
    }

    public function cloneProperty()
    {
        return null;
    }

    public function convertJSONToType($inputVarName = 'input')
    {
        $key = $this->key;
        return "\$$key = \${$inputVarName}['$key'];";
    }

    public function convertTypeToJSON($outputVarName = 'output')
    {
        $key = $this->key;
        return "\${$outputVarName}['$key'] = \$this->$key;";
    }

    protected function getOrNull($key)
    {
        return isset($this->schema[$key]) ? $this->schema[$key] : null;
    }

    public function generateSubTypes(SchemaToClass $generator)
    {
    }

}
