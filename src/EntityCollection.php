<?php

declare(strict_types = 1);

namespace ObjectValue;

use \ArrayIterator;
use \ArrayAccess;
use \Countable;
use \IteratorAggregate;

/**
 * Class EntityCollection
 * @package ObjectValue
 */
class EntityCollection implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * @var Entity[]
     */
    private $entities;

    /**
     * @param Entity[] $elements
     */
    public function __construct(array $elements = [])
    {
        $this->entities = $elements;
    }

    /**
     * @return Entity[]
     */
    public function toArray()
    {
        return $this->entities;
    }

    /**
     * @return Entity|false
     */
    public function first()
    {
        return reset($this->entities);
    }

    /**
     * @return Entity|bool
     */
    public function last()
    {
        return end($this->entities);
    }

    /**
     * @return int|null|string
     */
    public function key()
    {
        return key($this->entities);
    }

    /**
     * @return array
     */
    public function next()
    {
        return next($this->entities);
    }

    /**
     * @return Entity|bool
     */
    public function current()
    {
        return current($this->entities);
    }

    /**
     * @param int|string $key
     * @return null|Entity
     */
    public function remove($key): ?Entity
    {
        if (!isset($this->entities[$key]) && !array_key_exists($key, $this->entities)) {
            return null;
        }

        $removed = $this->entities[$key];
        unset($this->entities[$key]);

        return $removed;
    }

    /**
     * @param int|string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->containsKey($offset);
    }

    /**
     * @param int|string $offset
     * @return null|Entity
     */
    public function offsetGet($offset): ?Entity
    {
        return $this->get($offset);
    }

    /**
     * @param int|string $offset
     * @param Entity     $value
     * @return $this
     */
    public function offsetSet($offset, $value): self
    {
        if (!isset($offset)) {
            $this->add($value);
        } else {
            $this->set($offset, $value);
        }

        return $this;
    }

    /**
     * @param int|string $offset
     * @return $this
     */
    public function offsetUnset($offset): self
    {
        $this->remove($offset);

        return $this;
    }

    /**
     * @param mixed $key
     * @return bool
     */
    public function containsKey($key): bool
    {
        return isset($this->entities[$key]) || array_key_exists($key, $this->entities);
    }

    /**
     * @param $key
     * @return Entity|null
     */
    public function get($key): ?Entity
    {
        return $this->entities[$key] ?? null;
    }

    /**
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->entities);
    }

    /**
     * @return Entity[]
     */
    public function getValues(): array
    {
        return array_values($this->entities);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->entities);
    }

    /**
     * @param string|int $key
     * @param Entity     $value
     * @return $this
     */
    public function set($key, Entity $value): self
    {
        $this->entities[$key] = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function add($element)
    {
        $this->entities[] = $element;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty()
    {
        return empty($this->entities);
    }

    /**
     * Required by interface IteratorAggregate.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->entities);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return get_class($this);
    }

    /**
     * @return $this
     */
    public function clear(): self
    {
        $this->entities = [];

        return $this;
    }
}
