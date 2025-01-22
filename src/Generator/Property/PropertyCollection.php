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

    public static function fromArray(array $properties): PropertyCollection
    {
        $collection = new PropertyCollection();
        $collection->properties = array_values($properties);
        return $collection;
    }

    public function add(PropertyInterface $propertyGenerator): void
    {
        $this->properties[] = $propertyGenerator;
    }

    public function generateJSONToTypeConversionCode(string $inputVarName = 'input', bool $object = false): string
    {
        $conv = array_map(fn ($p) => $p->convertJSONToType($inputVarName, $object), $this->properties);
        return join("\n", $conv);
    }

    public function generateTypeToJSONConversionCode(string $outputVarName = 'output'): string
    {
        $conv = array_map(fn ($p) => $p->convertTypeToJSON($outputVarName), $this->properties);
        return join("\n", $conv);
    }

    public function hasPropertyWithKey(string $key): bool
    {
        foreach ($this->properties as $p) {
            if ($p->key() === $key) {
                return true;
            }
        }

        return false;
    }

    public function filter(PropertyCollectionFilter $filter): PropertyCollection
    {
        $matching = [];

        foreach ($this->properties as $property) {
            if ($filter->apply($property)) {
                $matching[] = $property;
            }
        }

        return PropertyCollection::fromArray($matching);
    }

    public function isOptional(PropertyInterface $prop): bool
    {
        return $prop instanceof OptionalPropertyDecorator || $prop instanceof DefaultPropertyDecorator;
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
