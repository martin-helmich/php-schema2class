<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

/**
 * @template-implements \Iterator<PropertyInterface>
 */
class PropertyCollection implements \Iterator
{
    /** @var PropertyInterface[] */
    private array $properties = [];

    private int $current = 0;

    public function add(PropertyInterface $propertyGenerator): void
    {
        $this->properties[] = $propertyGenerator;
    }

    /**
     * @param string $inputVarName
     * @param bool   $object
     * @return string
     */
    public function generateJSONToTypeConversionCode(string $inputVarName = 'input', bool $object = false): string
    {
        $conv = [];

        foreach ($this->properties as $generator) {
            $conv[] = $generator->convertJSONToType($inputVarName, $object);
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

    public function isOptional(PropertyInterface $prop): bool
    {
        return $prop instanceof OptionalPropertyDecorator;
    }

    public function current(): PropertyInterface
    {
        return $this->properties[$this->current];
    }

    public function next(): void
    {
        $this->current ++;
    }

    public function key(): int
    {
        return $this->current;
    }

    public function valid(): bool
    {
        return $this->current < count($this->properties);
    }

    public function rewind(): void
    {
        $this->current = 0;
    }


}
