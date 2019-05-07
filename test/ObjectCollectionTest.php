<?php

declare(strict_types = 1);

namespace ObjectValue\Test;

use ObjectValue\Entity;
use ObjectValue\EntityCollection;
use PHPUnit\Framework\TestCase;

/**
 * Class ObjectTest
 * @package ObjectValue\Test
 */
class ObjectCollectionTest extends TestCase
{
    /**
     * Test collection iterator
     */
    public function testCollectionIterator()
    {
        $object1 = (new Entity())->set('id', 1);
        $object2 = (new Entity())->set('id', 2);
        $object3 = (new Entity())->set('id', 3);
        $object4 = (new Entity())->set('id', 4);

        $collection = new EntityCollection();
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

    /**
     * Test retrieve array of entities
     */
    public function testCollectionGetArrayReturnsObjects()
    {
        $data = [
            (new Entity())->set('id', 1),
            (new Entity())->set('id', 2),
        ];

        $collection = new EntityCollection($data);

        $this->assertEquals($data, $collection->toArray());
    }

    /**
     * Test collection first() and last()
     */
    public function testCollectionFirstAndLastReturnsExpected()
    {
        $data = [
            0 => (new Entity())->set('id', 1),
            1 => (new Entity())->set('id', 2),
            2 => (new Entity())->set('id', 3),
        ];
        $collection = new EntityCollection($data);

        $this->assertEquals($data[2], $collection->last());
        $this->assertEquals(2, $collection->key());
        $this->assertEquals($data[0], $collection->first());
        $this->assertEquals(0, $collection->key());
    }

    /**
     * Test set will set specific key
     */
    public function testCollectionSetWillAddKey()
    {
        $entity = (new Entity())->set('id', 1);
        $collection = new EntityCollection();
        $collection->set('batman', $entity);
        $this->assertEquals(['batman'], $collection->getKeys());
        $this->assertEquals($entity, $collection->get('batman'));
    }

    /**
     * Test isEmpty() returns expected
     */
    public function testCollectionIsEmptyReturnsExpected()
    {
        $entity = (new Entity())->set('id', 1);
        $collection = new EntityCollection();
        $this->assertTrue($collection->isEmpty());
        $collection->set('batman', $entity);
        $this->assertFalse($collection->isEmpty());
    }

    /**
     * Test isEmpty() returns expected
     */
    public function testCollectionClear()
    {
        $collection = new EntityCollection([(new Entity())->set('id', 1)]);
        $this->assertTrue($collection->clear()->isEmpty());
    }

    /**
     * Test getValues returns values without keys
     */
    public function testCollectionGetValues()
    {
        $data = [
            'key1' => (new Entity())->set('id', 1),
            'key2' => (new Entity())->set('id', 2),
        ];
        $collection = new EntityCollection($data);
        $this->assertEquals(array_values($data), $collection->getValues());
    }

    /**
     * Test toString will return current class name
     */
    public function testCollectionToStringReturnsClassName()
    {
        $collection = new EntityCollection();
        $this->assertEquals(EntityCollection::class, $collection->__toString());
        $this->assertEquals(EntityCollection::class, (string) $collection);
        $this->assertEquals(EntityCollection::class, strval($collection));
    }

    /**
     * Test collection remove key that do not exist will return null
     */
    public function testRemoveMissingReturnsNull()
    {
        $collection = new EntityCollection();
        $collection->set(0, (new Entity())->set('id', 1));
        $this->assertNull($collection->remove('unknown'));
    }

    /**
     * Test collection remove existing
     */
    public function testRemoveExisting()
    {
        $data = [
            0 => (new Entity())->set('id', 1),
            1 => (new Entity())->set('id', 2),
            2 => (new Entity())->set('id', 3),
        ];
        $collection = new EntityCollection($data);

        $this->assertEquals($data[1], $collection->remove(1));
        $this->assertEquals($data[0], $collection->remove(0));
        $this->assertEquals(1, $collection->count());
        $this->assertEquals($data[2], $collection->first());
    }

    /**
     * Test collection foreach array access
     */
    public function testCollectionArrayAccess()
    {
        $data = [
            'key1' => (new Entity())->set('id', 1),
            'key2' => (new Entity())->set('id', 2),
            'key3' => (new Entity())->set('id', 3),
        ];
        $collection = new EntityCollection($data);

        foreach ($collection as $key => $value) {
            $this->assertArrayHasKey($key, $data);
            $this->assertEquals($data[$key], $value);
        }
    }

    /**
     * Test collection foreach array access
     */
    public function testCollectionArrayAccessKeyExist()
    {
        $data = [
            'key1' => (new Entity())->set('id', 1),
            'key2' => (new Entity())->set('id', 2),
        ];
        $entity = (new Entity())->set('id', 3);
        $collection = new EntityCollection($data);
        $collection['key3'] = $entity;

        $this->assertTrue(isset($collection['key3']));
        $this->assertTrue(array_key_exists('key3', $collection->toArray()));
        $this->assertEquals($collection['key3'], $entity);

        unset($collection['key3']);
        $this->assertFalse($collection->containsKey('key3'));
    }

    /**
     * Test add() will add new entity with incrementing int key
     */
    public function testCollectionArrayAccessAdd()
    {
        $entity = (new Entity())->set('id', 3);
        $collection = new EntityCollection();
        $collection->add($entity);
        $collection->add($entity);

        $this->assertEquals([0 => $entity, 1 => $entity], $collection->toArray());
    }

    /**
     * Test set array value will add new array value to list
     */
    public function testCollectionArraySetArrayWillAdd()
    {
        $entity = (new Entity())->set('id', 3);
        $collection = new EntityCollection();
        $collection[] = $entity;
        $collection[] = [];

        $this->assertEquals([0 => $entity, 1 => []], $collection->toArray());
    }
}
