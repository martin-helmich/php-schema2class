<?php
declare(strict_types = 1);
namespace Helmich\Schema2Class\Generator\Property;

use Helmich\Schema2Class\Util\StringUtils;

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
        $convStr = join("\n", $conv);

        // This is crude, but: Some of the generated code contains incomplete
        // "match" statements (because it is the syntactically easiest solution
        // for some of the type mapping requirements).
        //
        // However, some type checkers like PHPStan may (rightfully so) complain
        // about the potentially incomplete match statements. So if the mapping
        // code contains a "match (true)", we catch the resulting UnhandledMatchErrors
        // in the generated code and convert it to an InvalidArgumentException
        // (because due to the nature of the mapping code, any unhandled match
        // is caused by invalid input).
        if (str_contains($convStr, "match (true)")) {
            $convStr = StringUtils::indentMultiline($convStr);
            $convStr = "try {\n" . $convStr . "\n} catch (\\UnhandledMatchError \$err) {\n    throw new \\InvalidArgumentException(\$err->getMessage(), 0, \$err);\n}";
        }

        return $convStr;
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
