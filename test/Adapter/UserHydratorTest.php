<?php

declare(strict_types=1);

namespace LmcTest\User\Repository\Db\Adapter;

use Laminas\Hydrator\ClassMethodsHydrator;
use Lmc\User\Repository\Db\Adapter\UserHydrator;
use Lmc\User\Repository\Db\Entity\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserHydrator::class)]
final class UserHydratorTest extends TestCase
{
    public function testHydrate(): void
    {
        $hydrator = new UserHydrator(new ClassMethodsHydrator());
        $entity   = new User();
        $entity   = $hydrator->hydrate([
            'id'           => '1',
            'username'     => 'foo',
            'email'        => 'foo@bar.com',
            'display_name' => 'bar',
            'password'     => 'xyz',
            'state'        => 1,
            'roles'        => ['foo', 'bar'],
        ], $entity);
        $this->assertEquals('foo', $entity->getUsername());
        $this->assertEquals('foo@bar.com', $entity->getEmail());
        $this->assertEquals('bar', $entity->getDisplayName());
        $this->assertEquals('xyz', $entity->getPassword());
        $this->assertEquals(1, $entity->getState());
        $this->assertEquals('1', $entity->getId());
        $this->assertEquals(['foo', 'bar'], $entity->getRoles());
    }

    public function testExtract(): void
    {
        $hydrator = new UserHydrator(new ClassMethodsHydrator());
        $entity   = new User();
        $entity->setUsername('foo');
        $entity->setEmail('foo@bar.com');
        $entity->setDisplayName('bar');
        $entity->setPassword('xyz');
        $entity->setState(1);
        $entity->setId('0');
        $entity->setRoles(['foo', 'bar']);
        $data = $hydrator->extract($entity);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals([
            'username'     => 'foo',
            'email'        => 'foo@bar.com',
            'display_name' => 'bar',
            'password'     => 'xyz',
            'state'        => 1,
            'id'           => '0',
            'roles'        => ['foo', 'bar'],
        ], $data);
    }

    public function testExtractNullId(): void
    {
        $entity   = new User();
        $hydrator = new UserHydrator(new ClassMethodsHydrator());
        $data     = $hydrator->extract($entity);
        $this->assertArrayHasKey('id', $data);
    }

    public function testMapField(): void
    {
        $entity = new User();
        $entity->setUsername('foo');
        $entity->setEmail('foo@bar.com');
        $entity->setDisplayName('bar');
        $entity->setPassword('xyz');
        $entity->setState(1);
        $entity->setId('0');
        $entity->setRoles(['foo', 'bar']);

        $hydrator = new UserHydrator(new ClassMethodsHydrator(), 'user_id');

        $data = $hydrator->extract($entity);
        $this->assertArrayHasKey('user_id', $data);
        $this->assertEquals([
            'username'     => 'foo',
            'email'        => 'foo@bar.com',
            'display_name' => 'bar',
            'password'     => 'xyz',
            'state'        => 1,
            'user_id'      => '0',
            'roles'        => ['foo', 'bar'],
        ], $data);

        $data      = [
            'user_id' => 4,
        ];
        $newEntity = $hydrator->hydrate($data, $entity);
        $this->assertEquals(4, $newEntity->getId());
    }
}
