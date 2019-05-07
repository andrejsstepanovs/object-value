<?php

declare(strict_types = 1);

namespace ObjectValue;

use ObjectValue\Exceptions\LockedAttributesException;
use ObjectValue\Exceptions\LockedValuesException;
use ObjectValue\Exceptions\MissingException;
use \ArrayObject;

/**
 * Class Entity
 * @package ObjectValue
 */
class Entity extends ArrayObject
{
    /**
     * Do not allow value change
     *
     * @var bool
     */
    private $lockValues;

    /**
     * Do not allow any new attributes
     *
     * @var bool
     */
    private $lockAttributes;

    /**
     * Do not allow to change existing attribute value types
     *
     * @var bool
     */
    private $lockTypes;

    /**
     * @return $this
     */
    public function lockValues(): self
    {
        $this->lockValues = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLockValues(): bool
    {
        return (bool) $this->lockValues;
    }

    /**
     * @return $this
     */
    public function lockAttributes(): self
    {
        $this->lockAttributes = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLockAttributes(): bool
    {
        return (bool) $this->lockAttributes;
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
        if ($this->isLockValues()) {
            throw new LockedValuesException('Can not set "'.$name.'" because object ['.$this.'] is locked');
        }
        if ($this->isLockAttributes() && !$this->exists($name)) {
            throw new LockedAttributesException('Can not set new attribute "'.$name.'" because object ['.$this.'] attributes are locked ');
        }
        parent::offsetSet($name, $value);
    }

    /**
     * @param string $name
     */
    public function offsetUnset($name)
    {
        if ($this->isLockValues()) {
            throw new LockedValuesException('Can not remove "'.$name.'" because object ['.$this.'] values are locked');
        }
        if ($this->isLockAttributes()) {
            throw new LockedAttributesException('Can not remove "'.$name.'" because object ['.$this.'] attributes are locked ');
        }
        if (!$this->offsetExists($name)) {
            throw new MissingException('Can not remove "'.$name.'" because it is missing ['.$this.']');
        }
        parent::offsetUnset($name);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return get_class($this);
    }
}
