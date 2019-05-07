<?php

declare(strict_types = 1);

namespace ObjectValue\Test;

use ObjectValue\Entity;
use PHPUnit\Framework\TestCase;

/**
 * Class ObjectTest
 * @package ObjectValue\Test
 */
class ObjectTest extends TestCase
{
    /**
     * Test setter returns self
     */
    public function testSetterReturnSelf()
    {
        $object = (new Entity())->set('apple', 'green');
        $this->assertInstanceOf(Entity::class, $object);
    }

    /**
     * @return array
     */
    public function setterDataProvider(): array
    {
        return [
            ['name', 'Test name'],
            ['time', time()],
            ['number', '100,991'],
            ['int', 12345670],
            ['float', (float)12345.29],
            ['bank', '00001111222233334444'],
            ['words', 'Test small sentence'],
            ['text', 'Minima ducimus dolore incidunt iusto dolorem. Saepe officiis saepe culpa sunt neque nobis.'],
            ['datetime', new \DateTime()],
        ];
    }

    /**
     * @dataProvider setterDataProvider
     * @param string $name
     * @param mixed  $value
     */
    public function testSetterAndGetterWithSimpleValues(string $name, $value)
    {
        $response = (new Entity())->set($name, $value)->get($name);
        $this->assertEquals($value, $response);
    }

    /**
     * Test removing existing key
     */
    public function testRemoveExistingWIllDeletesKey()
    {
        $object = (new Entity())
            ->set('apple', 'green')
            ->set('banana', 'yellow')
        ;

        $this->assertEquals($object->getAll(), ['apple' => 'green', 'banana' => 'yellow']);

        $object->remove('apple');
        $this->assertEquals($object->getAll(), ['banana' => 'yellow']);

        $object->remove('banana');
        $this->assertEquals($object->getAll(), []);
    }

    /**
     *
     */
    public function testExists()
    {
        $object = (new Entity())
            ->set('kiwi', 'green')
            ->set('orange', 'orange')
        ;

        $this->assertTrue($object->exists('kiwi'));
        $this->assertTrue($object->exists('orange'));
        $this->assertFalse($object->exists('banana'));
    }

    /**
     * @expectedException \ObjectValue\Exceptions\MissingException
     */
    public function testRemoveNotExistingWillThrowException()
    {
        (new Entity())->set('kiwi', 'green')->remove('banana');
    }

    /**
     * @expectedException \ObjectValue\Exceptions\MissingException
     */
    public function testGetNotExistingKeyWillThrowException()
    {
        (new Entity())->set('kiwi', 'green')->get('banana');
    }

    /**
     * Test array access setter and getter (that is done by offsetSet offsetGet)
     */
    public function testArrayAccessSetterAndGetter()
    {
        $object = (new Entity())->set('apple', 'green');
        $object['banana'] = 'yellow';

        $this->assertEquals('green', $object['apple']);
        $this->assertEquals('yellow', $object['banana']);
    }

    /**
     * Test array access isset that is done by offsetExists()
     */
    public function testArrayAccessOffsetExists()
    {
        $object = (new Entity())->set('apple', 'green');

        $this->assertTrue(isset($object['apple']));
        $this->assertFalse(isset($object['banana']));
    }

    /**
     * Test array access unset that is done by offsetUnset()
     */
    public function testArrayAccessOffsetUnset()
    {
        $object = (new Entity())->set('apple', 'green')->set('kiwi', 'green');

        unset($object['kiwi']);

        $this->assertTrue($object->exists('apple'));
        $this->assertFalse($object->exists('kiwi'));
    }

    /**
     * Test Countable interface
     */
    public function testCountable()
    {
        $object = (new Entity());
        $this->assertEquals(0, $object->count());
        $this->assertEquals(0, count($object));

        $object->set('apple', 'green')->set('kiwi', 'green');
        $this->assertEquals(2, $object->count());
        $this->assertEquals(2, count($object));
    }

    /**
     * Test controller setter
     */
    public function testController()
    {
        $data = ['apple' => 'green', 'banana' => 'yellow'];
        $object = (new Entity($data));

        $this->assertEquals($object->getAll(), $data);
    }

    /**
     * Test by default object is not locked
     */
    public function testByDefaultObjectIsNotLocked()
    {
        $this->assertFalse((new Entity())->isLockValues());
    }

    /**
     * Test lock object
     */
    public function testLockMakesObjectLocked()
    {
        $object = (new Entity());
        $object->set('apple', 'green');
        $object->lockValues();
        $this->assertTrue($object->isLockValues());
    }

    /**
     * Test locked object dont allow to set
     * @expectedException \ObjectValue\Exceptions\LockedValuesException
     */
    public function testLockedObjectNotAllowToSet()
    {
        $object = (new Entity());
        $object->set('apple', 'green');
        $object->lockValues();
        $object->set('banana', 'yellow');
    }

    /**
     * Test locked object dont allow to set
     * @expectedException \ObjectValue\Exceptions\LockedValuesException
     */
    public function testLockedObjectNotAllowToRemove()
    {
        $object = (new Entity());
        $object->set('apple', 'green');
        $object->lockValues();
        $object->remove('apple');
    }

    /**
     * Test locked object dont allow to set
     * @expectedException \ObjectValue\Exceptions\LockedValuesException
     */
    public function testLockedObjectNotAllowToUnset()
    {
        $object = (new Entity());
        $object->set('apple', 'green');
        $object->lockValues();
        unset($object['apple']);
    }

    /**
     * Test that iterator will loop values
     */
    public function testIteratorMagic()
    {
        $object = (new Entity(['apple' => 'green']));

        foreach ($object as $name => $value) {
            $this->assertEquals('apple', $name);
            $this->assertEquals('green', $value);
        }
    }

    /**
     * Test that iterator will loop values
     */
    public function testIteratorObject()
    {
        $object = (new Entity(['apple' => 'green', 'banana' => 'yellow']));

        $iterator = $object->getIterator();

        $iterator->rewind();
        $this->assertEquals('green', $iterator->current());
        $this->assertEquals('apple', $iterator->key());

        $iterator->next();
        $this->assertEquals('yellow', $iterator->current());
        $this->assertEquals('banana', $iterator->key());

        $iterator->rewind();
        $this->assertEquals('apple', $iterator->key());
    }

    /**
     * Test toString returns full object name
     */
    public function testToString()
    {
        include_once 'MyTestEntity.php';
        $object = new MyTestEntity();
        $expected = MyTestEntity::class;

        $this->assertEquals($expected, $object->__toString());
        $this->assertEquals($expected, (string) $object);
        $this->assertEquals($expected, strval($object));
    }
}
