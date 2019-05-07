<?php

namespace ObjectValue\Test;

/**
 * Interface TestEntityInterface
 * @package ObjectValue\Test
 */
interface TestEntityInterface
{
    public function setId(int $id): self;
    public function getId(): int;
}
