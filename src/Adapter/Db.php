<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Db\Adapter;

use Laminas\Db\Adapter\Driver\ResultInterface;
use Lmc\User\Repository\AdapterInterface;
use Lmc\User\Repository\UserInterface;

use function assert;

final class Db extends AbstractDbAdapter implements AdapterInterface
{
    public function findByEmail(string $email): ?UserInterface
    {
        $select = $this->getSelect()->where(['email' => $email]);
        $entity = $this->innerSelect($select)->current();
        assert($entity instanceof UserInterface || $entity === null);
        $this->getEventManager()->trigger('find', $this, ['entity' => $entity]);
        return $entity;
    }

    public function findById(string|int $id): ?UserInterface
    {
        $select = $this->getSelect()->where([$this->idColumn => $id]);
        $entity = $this->innerSelect($select)->current();
        assert($entity instanceof UserInterface || $entity === null);
        $this->getEventManager()->trigger('find', $this, ['entity' => $entity]);
        return $entity;
    }

    public function findByUsername(string $username): ?UserInterface
    {
        $select = $this->getSelect()->where(['username' => $username]);
        $entity = $this->innerSelect($select)->current();
        assert($entity instanceof UserInterface || $entity === null);
        $this->getEventManager()->trigger('find', $this, ['entity' => $entity]);
        return $entity;
    }

    public function insert(UserInterface $user): mixed
    {
        $result = $this->innerInsert($user);
        assert($result instanceof ResultInterface);
        /** @var int $id */
        $id = $result->getGeneratedValue();
        $user->setId($id);
        return $result;
    }

    public function update(UserInterface $user): mixed
    {
        /** @psalm-suppress InvalidArrayOffset */
        $where = [$this->idColumn => $user->getId()];
        return $this->innerUpdate($user, $where);
    }

    public function delete(UserInterface $user): mixed
    {
        /** @psalm-suppress InvalidArrayOffset */
        $where = [$this->idColumn => $user->getId()];
        return $this->innerDelete($where);
    }
}
