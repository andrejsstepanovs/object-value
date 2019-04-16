<?php

declare(strict_types = 1);

namespace ObjectValue\Test;

use ObjectValue\Object;
use ObjectValue\ObjectCollection;
use PHPUnit\Framework\TestCase;
use Faker\Factory as FakerFactory;

/**
 * Class ObjectTest
 * @package ObjectValue\Test
 */
class ObjectCollectionTest extends TestCase
{
    /**
     *
     */
    public function testCollectionInit()
    {
        $object1 = (new Object())->set('id', 1);
        $object2 = (new Object())->set('id', 1);
        $object3 = (new Object())->set('id', 1);
        $object4 = (new Object())->set('id', 1);

        $collection = new ObjectCollection();
        $collection->add($object1);
        $collection->add($object2);
        $collection->add($object3);
        $collection->add($object4);

        $this->assertEquals(4, $collection->count());

        $this->assertEquals($object1, $collection->current());

        $collection->next();
        $this->assertEquals($object2, $collection->current());

        $collection->next();
        $this->assertEquals($object3, $collection->current());

        $collection->next();
        $this->assertEquals($object4, $collection->current());
    }
}
