<?php

declare (strict_types=1);
namespace Test\Entities;

use ObjectValue\ObjectCollection;
use ObjectValue\Entity as AbstractEntity;
use Test\Entities\Interfaces\PersonInterface as EntityInterface;
/**
 * Class Person
 * @package Test\Entities
 */
class Person extends AbstractEntity implements EntityInterface
{
    /**
     * @param \stdClass $dad
     * @return $this
     */
    public function setDad(\stdClass $dad) : self
    {
        $this->set('dad', $dad);
        return $this;
    }
    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id) : self
    {
        $this->set('id', $id);
        return $this;
    }
    /**
     * @return null|int
     */
    public function getId() : ?int
    {
        return $this->get('id');
    }
    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name) : self
    {
        $this->set('name', $name);
        return $this;
    }
    /**
     * @return null|string
     */
    public function getName() : ?string
    {
        return $this->get('name');
    }
    /**
     * @param string $lastName
     * @return $this
     */
    public function setLastName(string $lastName) : self
    {
        $this->set('last_name', $lastName);
        return $this;
    }
    /**
     * @return null|string
     */
    public function getLastName() : ?string
    {
        return $this->get('last_name');
    }
    /**
     * @param int $age
     * @return $this
     */
    public function setAge(int $age) : self
    {
        $this->set('age', $age);
        return $this;
    }
    /**
     * @return null|int
     */
    public function getAge() : ?int
    {
        return $this->get('age');
    }
    /**
     * @param ObjectCollection $mom
     * @return $this
     */
    public function setMom(ObjectCollection $mom) : self
    {
        $this->set('mom', $mom);
        return $this;
    }
    /**
     * @return null|ObjectCollection
     */
    public function getMom() : ?ObjectCollection
    {
        return $this->get('mom');
    }
    /**
     * @return null|\stdClass
     */
    public function getDad() : ?\stdClass
    {
        return $this->get('dad');
    }
}