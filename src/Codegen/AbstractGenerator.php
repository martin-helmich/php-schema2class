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
        array_splice($this->nodes, $index, 0, [$node]);

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

        return array_splice($this->nodes, $index, 1)[0];
    }

    public function clear(): self
    {
        $this->nodes = [];

        return $this;
    }

    /**
     * @psalm-param callable(Node): Node $callback
     */
    public function walk(callable $callback): self
    {
        array_walk($this->nodes, $callback);

        return $this;
    }

    /**
     * @psalm-param ($mode is 0 ? callable(Node) : callable(Node, int) ) $callback
     */
    public function filter(callable $callback, int $mode = 0): self
    {
        $this->nodes = array_filter($this->nodes, $callback, $mode);
        $this->nodes = array_values($this->nodes);

        return $this;
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

    public function last(): ?Node
    {
        if (empty($this->nodes)) {
            return null;
        }

        return $this->nodes[array_key_last($this->nodes)];
    }

    public function count(): int
    {
        return count($this->nodes);
    }

    protected function isValidIndex(int $index): bool
    {
        return array_key_exists($index, $this->nodes);
    }
}
