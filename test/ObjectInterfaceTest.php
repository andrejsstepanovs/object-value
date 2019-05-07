<?php

declare(strict_types = 1);

namespace ObjectValue\Test;

use ObjectValue\Builder;
use Test\Entities\Person;
use Test\Entities\Interfaces\PersonInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class ObjectInterfaceTest
 * @package ObjectValue\Test
 */
class ObjectInterfaceTest extends TestCase
{
    /**
     *
     */
    public function testFactoryUsingInterface()
    {
        if (file_exists(__DIR__.'/Entities/Person.php')) {
            unlink(__DIR__.'/Entities/Person.php');
        }
        include_once 'Entities/Interfaces/PersonInterface.php';

        $config = [];
        $config['entities_namespace'] = 'Test\Entities';
        $config['entities_path']      = __DIR__.'/Entities';
        $config['interface_path']     = __DIR__.'/Entities/Interfaces/';

        $builder = new Builder($config);
        /** @var PersonInterface|Person $entity */
        $entity = $builder->getFromInterface(PersonInterface::class);

        $entity->setId(10);
        $entity->setName('Bob');
        $entity->setLastName('Baum');
        $entity->setAge(32);

        $dad = new \stdClass();
        $dad->name = 'papa';
        $entity->setDad($dad);

        $data = [
            'id'        => 10,
            'name'      => 'Bob',
            'last_name' => 'Baum',
            'age'       => 32,
            'dad'       => $dad,
        ];

        $this->assertEquals($data, $entity->getAll());
    }
}
