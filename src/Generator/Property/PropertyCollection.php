<?php
namespace Helmich\Schema2Class\Generator\Property;

class PropertyCollection implements \Iterator
{
    /** @var PropertyInterface[] */
    private $properties = [];

    private $current = 0;

    public function add(PropertyInterface $propertyGenerator)
    {
        $this->properties[] = $propertyGenerator;
    }

    /**
     * @param string $inputVarName
     * @return string
     */
    public function generateJSONToTypeConversionCode($inputVarName = 'input')
    {
        $conv = [];

        foreach ($this->properties as $generator) {
            $conv[] = $generator->convertJSONToType($inputVarName);
        }

        return join("\n", $conv);
    }

    /**
     * @param string $outputVarName
     * @return string
     */
    public function generateTypeToJSONConversionCode($outputVarName = 'output')
    {
        $conv = [];

        foreach ($this->properties as $generator) {
            $conv[] = $generator->convertTypeToJSON($outputVarName);
        }

        return join("\n", $conv);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasPropertyWithKey($key)
    {
        foreach ($this->properties as $p) {
            if ($p->key() === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return PropertyInterface[]
     */
    public function filterRequired()
    {
        return array_filter($this->properties, function($p) {
            return !($p instanceof OptionalPropertyDecorator);
        });
    }

    /**
     * @return PropertyInterface[]
     */
    public function filterOptional()
    {
        return array_filter($this->properties, function($p) {
            return $p instanceof OptionalPropertyDecorator;
        });
    }

    public function current()
    {
        return $this->properties[$this->current];
    }

    public function next()
    {
        $this->current ++;
    }

    public function key()
    {
        return $this->current;
    }

    public function valid()
    {
        return $this->current < count($this->properties);
    }

    public function rewind()
    {
        $this->current = 0;
    }


}