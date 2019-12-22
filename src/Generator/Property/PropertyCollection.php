<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

class PropertyCollection implements \Iterator
{
    /** @var PropertyInterface[] */
    private $properties = [];

    private $current = 0;

    public function add(PropertyInterface $propertyGenerator): void
    {
        $this->properties[] = $propertyGenerator;
    }

    /**
     * @param string $inputVarName
     * @return string
     */
    public function generateJSONToTypeConversionCode(string $inputVarName = 'input'): string
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
    public function generateTypeToJSONConversionCode(string $outputVarName = 'output'): string
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
    public function hasPropertyWithKey(string $key): bool
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
    public function filterRequired(): array
    {
        return array_filter($this->properties, function($p) {
            return !($p instanceof OptionalPropertyDecorator);
        });
    }

    /**
     * @return PropertyInterface[]
     */
    public function filterOptional(): array
    {
        return array_filter($this->properties, function($p) {
            return $p instanceof OptionalPropertyDecorator;
        });
    }

    public function current(): PropertyInterface
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