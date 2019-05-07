<?php

namespace Test\Entities\Interfaces;

use ObjectValue\ObjectCollection;

/**
 * Interface PersonInterface
 * @package ObjectValue\Entities\Interfaces
 */
interface PersonInterface
{
    public function setDad(\stdClass $value);

    public function setId(int $value);
    public function getId(): ?int;
    public function setName(string $value);
    public function getName(): ?string;
    public function setLastName(string $value);
    public function getLastName(): ?string;
    public function setAge(int $value);
    public function getAge(): ?int;
    public function setMom(ObjectCollection $value);
    public function getMom(): ?ObjectCollection;
    public function getDad(): ?\stdClass;
}
