<?php

declare(strict_types=1);

namespace LmcTest\User\Repository\Db\Options;

use InvalidArgumentException;
use Lmc\User\Repository\Db\Entity\User;
use Lmc\User\Repository\Db\Options\Options;
use LmcTest\User\Repository\Db\Assets\TestUserEntity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Options::class)]
final class OptionsTest extends TestCase
{
    public function testCoreOptionsDefault(): void
    {
        $coreOptions = new Options();
        $this->assertEquals('user', $coreOptions->getTableName());
        $this->assertEquals(User::class, $coreOptions->getUserEntityClass());
        $this->assertEquals(',', $coreOptions->getRolesDelimiter());
        $this->assertEquals('id', $coreOptions->getIdFieldName());
    }

    public function testCoreOptionsCustoms(): void
    {
        $coreOptions = new Options([
            'tableName'       => 'foo',
            'userEntityClass' => TestUserEntity::class,
            'rolesDelimiter'  => ',',
            'idFieldName'     => 'user_id',
        ]);
        $this->assertEquals('foo', $coreOptions->getTableName());
        $this->assertEquals(TestUserEntity::class, $coreOptions->getUserEntityClass());
        $this->assertEquals(',', $coreOptions->getRolesDelimiter());
        $this->assertEquals('user_id', $coreOptions->getIdFieldName());
    }

    public function testCoreOptionsSetGet(): void
    {
        $coreOptions = new Options();
        $this->assertEquals('foo', $coreOptions->setTableName('foo')->getTableName());
        $this->assertEquals(
            TestUserEntity::class,
            $coreOptions->setUserEntityClass(TestUserEntity::class)
                ->getUserEntityClass()
        );
        $this->assertEquals(',', $coreOptions->setRolesDelimiter(',')->getRolesDelimiter());
        $this->assertEquals('user_id', $coreOptions->setIdFieldName('user_id')->getIdFieldName());
    }

    public function testNotExistUserEntityClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Options([
            'userEntityClass' => 'foo',
        ]);
    }

    public function testInvalidUserEntityClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Options([
            'userEntityClass' => stdClass::class,
        ]);
    }
}
