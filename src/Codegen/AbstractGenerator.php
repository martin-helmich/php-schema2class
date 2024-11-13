<?php

declare(strict_types=1);

namespace Helmich\Schema2Class\Codegen;

use PhpParser\Node;

abstract class AbstractGenerator
{
    /**
     * @param Node[] $nodes
     */
    public function __construct(protected array $nodes)
    {
    }

    public function insert(int $index, Node $node): self
    {
        array_splice($this->nodes, $index, 0, $node);

        return $this;
    }

    public function set(int $index, $value): self
    {
        if (!$this->isValidIndex($index)) {
            throw new \OutOfRangeException();
        }

        $this->nodes[$index] = $value;

        return $this;
    }

    public function remove(int $index): Node
    {
        if (!$this->isValidIndex($index)) {
            throw new \OutOfRangeException();
        }

        return array_splice($this->nodes, $index, 1, null)[0];
    }

    public function clear(): self
    {
        $this->nodes = [];

        return $this;
    }

    /**
     * @psalm-param callable(Node): Node $callback
     */
    public function apply(callable $callback): self
    {
        foreach ($this->nodes as &$value) {
            $value = $callback($value);
        }

        return $this;
    }

    public function filter(callable $callback = null): self
    {
        return new static(array_filter($this->nodes, $callback ?: 'boolval'));
    }

    public function find(Node $value): ?int
    {
        $offset = array_search($value, $this->nodes, true);

        return $offset === false ? null : $offset;
    }

    public function first(): Node
    {
        if (empty($this->nodes)) {
            throw new \UnderflowException();
        }

        return $this->nodes[0];
    }

    public function get(int $index): Node
    {
        if (!$this->isValidIndex($index)) {
            throw new \OutOfRangeException();
        }

        return $this->nodes[$index];
    }

    public function last(): Node
    {
        return $this->nodes[array_key_last($this->nodes)];
    }

    protected function isValidIndex(int $index): bool
    {
        return array_key_exists($index, $this->nodes);
    }
}
