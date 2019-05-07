<?php

declare(strict_types = 1);

namespace ObjectValue\Test;

use ObjectValue\Entity;
use PHPUnit\Framework\TestCase;

include_once 'MyTestEntity.php';

/**
 * Class ObjectTest
 * @package ObjectValue\Test
 */
class MyTestObjectTest extends TestCase
{
    /**
     * Test toString returns full object name
     */
    public function testToString()
    {
        $object = new MyTestEntity();
        $expected = MyTestEntity::class;

        $this->assertEquals($expected, $object->__toString());
        $this->assertEquals($expected, (string) $object);
        $this->assertEquals($expected, strval($object));
    }

    public function testSetterAndGetter()
    {
        $other  = new Entity(['type' => 'pro']);
        $object = new MyTestEntity();

        $object->setName('Bobby');
        $object->setObject($other);

        $expected = [
            'name'   => 'Bobby',
            'object' => $other,
        ];

        $this->assertEquals($expected, $object->getAll());
    }
}
