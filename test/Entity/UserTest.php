<?php

declare(strict_types=1);

namespace LmcTest\User\Repository\Db\Entity;

use Lmc\User\Repository\Db\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testConstruct(): void
    {
        $user = new User();
        $this->assertNull($user->getId());
        $this->assertNull($user->getUsername());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getDisplayName());
        $this->assertNull($user->getState());
        $this->assertNull($user->getPassword());
        $this->assertNull($user->getIdentity());
        $this->assertIsArray($user->getRoles());
        $this->assertEmpty($user->getRoles());
    }

    public function testSetGet(): void
    {
        $user = new User();
        $this->assertEquals(1, $user->setId(1)->getId());
        $this->assertEquals('1', $user->setId(1)->getIdentity());
        $this->assertEquals('1', $user->setId('1')->getId());
        $this->assertEquals('1', $user->setId(1)->getIdentity());
        $this->assertEquals('foo', $user->setUsername('foo')->getUsername());
        $this->assertEquals('foo', $user->setEmail('foo')->getEmail());
        $this->assertEquals('foo', $user->setDisplayName('foo')->getDisplayName());
        $this->assertEquals(1, $user->setState(1)->getState());
        $this->assertEquals(['foo'], $user->setRoles(['foo'])->getRoles());
        $this->assertEquals('foo', $user->setPassword('foo')->getPassword());
    }
}
