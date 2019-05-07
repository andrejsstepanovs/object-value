<?php

declare(strict_types = 1);

namespace ObjectValue\Test;

use ObjectValue\Entity;

class MyTestEntity extends Entity
{
    /**
     * @param Object $data
     * @return MyTestEntity
     */
    public function setObject(Entity $data): self
    {
        return $this->set('object', $data);
    }

    /**
     * @return null|Object
     */
    public function getObject(): ?Entity
    {
        return $this->get('object');
    }

    /**
     * @param string $name
     * @return MyTestEntity
     */
    public function setName(string $name): self
    {
        return $this->set('name', $name);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->get('name');
    }
}
