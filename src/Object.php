<?php

declare(strict_types = 1);

namespace ObjectValue;

use ObjectValue\Exceptions\LockedException;
use ObjectValue\Exceptions\MissingException;
use \ArrayObject;

/**
 * Class Object
 * @package ObjectValue
 */
class Object extends ArrayObject
{
    /**
     * To make object immutable lock() it
     *
     * @var bool
     */
    private $locked;

    /**
     * @return $this
     */
    public function lock(): self
    {
        $this->locked = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return (bool) $this->locked;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return $this
     */
    public function set(string $name, $value)
    {
        $this->offsetSet($name, $value);

        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->offsetGet($name);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function remove(string $name)
    {
        $this->offsetUnset($name);

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function exists(string $name)
    {
        return $this->offsetExists($name);
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->getArrayCopy();
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function offsetGet($name)
    {
        if ($this->exists($name)) {
            return parent::offsetGet($name);
        }
        throw new MissingException();
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function offsetSet($name, $value)
    {
        if ($this->isLocked()) {
            throw new LockedException('Can not set "'.$name.'" because object ['.get_class($this).'] is locked');
        }
        parent::offsetSet($name, $value);
    }

    /**
     * @param string $name
     */
    public function offsetUnset($name)
    {
        if ($this->isLocked()) {
            throw new LockedException('Can not remove "'.$name.'" because object ['.get_class($this).'] is locked');
        }
        if (!$this->offsetExists($name)) {
            throw new MissingException();
        }
        parent::offsetUnset($name);
    }
}
