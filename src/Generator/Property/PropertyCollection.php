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

    /**
     * @return PropertyInterface[]
     */
    public function filterRequired(): array
    {
        return array_filter($this->properties, fn ($p) => !$this->isOptional($p));
    }

    /**
     * @return PropertyInterface[]
     */
    public function filterOptional(): array
    {
        return array_filter($this->properties, fn ($p) => $this->isOptional($p));
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
