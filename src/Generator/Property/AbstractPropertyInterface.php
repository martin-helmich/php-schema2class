<?php
namespace Helmich\JsonStructBuilder\Generator\Property;

use Helmich\JsonStructBuilder\Generator\GeneratorContext;
use Helmich\JsonStructBuilder\Generator\SchemaToClass;

abstract class AbstractPropertyInterface implements PropertyInterface
{

    /** @var string */
    protected $key;

    /** @var array */
    protected $schema;

    /** @var string */
    protected $capitalizedName;

    /** @var GeneratorContext */
    protected $ctx;

    public function __construct($key, array $schema, GeneratorContext $ctx)
    {
        $this->key = $key;
        $this->schema = $schema;
        $this->capitalizedName = strtoupper($this->key[0]) . substr($this->key, 1);
        $this->ctx = $ctx;
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
        return "\$obj->$key = \${$inputVarName}['$key'];";
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