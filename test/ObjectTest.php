<?php

declare(strict_types = 1);

namespace ObjectValue\Test;

use ObjectValue\Object;
use PHPUnit\Framework\TestCase;
use Faker\Factory as FakerFactory;

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
        $object = (new Object())->set('apple', 'green');
        $this->assertInstanceOf(Object::class, $object);
    }

    /**
     * @return array
     */
    public function setterDataProvider(): array
    {
        $faker = FakerFactory::create();

        return [
            ['name', $faker->name],
            ['time', $faker->time],
            ['number', $faker->numerify()],
            ['int', $faker->numberBetween()],
            ['float', $faker->randomFloat()],
            ['bank', $faker->bankAccountNumber],
            ['words', $faker->words()],
            ['text', $faker->text()],
            ['datetime', $faker->dateTime],
        ];
    }

    /**
     * @dataProvider setterDataProvider
     * @param string $name
     * @param mixed  $value
     */
    public function testSetterAndGetterWithSimpleValues(string $name, $value)
    {
        $response = (new Object())->set($name, $value)->get($name);
        $this->assertEquals($value, $response);
    }

    /**
     * Test removing existing key
     */
    public function testRemoveExistingWIllDeletesKey()
    {
        $object = (new Object())
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
        $object = (new Object())
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
        (new Object())->set('kiwi', 'green')->remove('banana');
    }

    /**
     * @expectedException \ObjectValue\Exceptions\MissingException
     */
    public function testGetNotExistingKeyWillThrowException()
    {
        (new Object())->set('kiwi', 'green')->get('banana');
    }

    /**
     * Test array access setter and getter (that is done by offsetSet offsetGet)
     */
    public function testArrayAccessSetterAndGetter()
    {
        $object = (new Object())->set('apple', 'green');
        $object['banana'] = 'yellow';

        $this->assertEquals('green', $object['apple']);
        $this->assertEquals('yellow', $object['banana']);
    }

    /**
     * Test array access isset that is done by offsetExists()
     */
    public function testArrayAccessOffsetExists()
    {
        $object = (new Object())->set('apple', 'green');

        $this->assertTrue(isset($object['apple']));
        $this->assertFalse(isset($object['banana']));
    }

    /**
     * Test array access unset that is done by offsetUnset()
     */
    public function testArrayAccessOffsetUnset()
    {
        $object = (new Object())->set('apple', 'green')->set('kiwi', 'green');

        unset($object['kiwi']);

        $this->assertTrue($object->exists('apple'));
        $this->assertFalse($object->exists('kiwi'));
    }

    /**
     * Test Countable interface
     */
    public function testCountable()
    {
        $object = (new Object());
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
        $object = (new Object($data));

        $this->assertEquals($object->getAll(), $data);
    }

    /**
     * Test by default object is not locked
     */
    public function testByDefaultObjectIsNotLocked()
    {
        $this->assertFalse((new Object())->isLocked());
    }

    /**
     * Test lock object
     */
    public function testLockMakesObjectLocked()
    {
        $object = (new Object());
        $object->set('apple', 'green');
        $object->lock();
        $this->assertTrue($object->isLocked());
    }

    /**
     * Test locked object dont allow to set
     * @expectedException \ObjectValue\Exceptions\LockedException
     */
    public function testLockedObjectNotAllowToSet()
    {
        $object = (new Object());
        $object->set('apple', 'green');
        $object->lock();
        $object->set('banana', 'yellow');
    }

    /**
     * Test locked object dont allow to set
     * @expectedException \ObjectValue\Exceptions\LockedException
     */
    public function testLockedObjectNotAllowToRemove()
    {
        $object = (new Object());
        $object->set('apple', 'green');
        $object->lock();
        $object->remove('apple');
    }

    /**
     * Test locked object dont allow to set
     * @expectedException \ObjectValue\Exceptions\LockedException
     */
    public function testLockedObjectNotAllowToUnset()
    {
        $object = (new Object());
        $object->set('apple', 'green');
        $object->lock();
        unset($object['apple']);
    }

    /**
     * Test that iterator will loop values
     */
    public function testIteratorMagic()
    {
        $object = (new Object(['apple' => 'green']));

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
        $object = (new Object(['apple' => 'green', 'banana' => 'yellow']));

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
}
