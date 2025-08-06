<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Db\Adapter;

use Closure;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Hydrator\HydratorInterface;
use Lmc\User\Repository\UserInterface;

abstract class AbstractDbAdapter implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    protected ?Sql $sql = null;

    public function __construct(
        protected readonly Adapter $dbAdapter,
        protected readonly HydratorInterface $hydrator,
        protected readonly UserInterface $entityPrototype,
        protected readonly ?string $tableName = 'user',
        protected readonly ?string $idColumn = 'id',
    ) {
        $this->setEventManager(new EventManager());
    }

    protected function innerSelect(
        Select $select,
        ?UserInterface $entityPrototype = null,
        ?HydratorInterface $hydrator = null,
    ): HydratingResultSet {
        $statement = $this->getSql()->prepareStatementForSqlObject($select);
        $resultSet = new HydratingResultSet(
            $hydrator ?: $this->getHydrator(),
            $entityPrototype ?: $this->getEntityPrototype()
        );
        $resultSet->initialize($statement->execute());
        return $resultSet;
    }

    protected function innerInsert(
        UserInterface $entity,
        ?string $tableName = null,
        ?HydratorInterface $hydrator = null
    ): ResultInterface {
        $sql     = $this->getSql()->setTable($tableName ?? $this->getTableName());
        $insert  = $sql->insert();
        $rowData = $this->entityToArray($entity, $hydrator);
        $insert->values($rowData);
        $statement = $sql->prepareStatementForSqlObject($insert);
        return $statement->execute();
    }

    protected function innerUpdate(
        UserInterface $entity,
        string|array|Closure $where,
        ?string $tableName = null,
        ?HydratorInterface $hydrator = null
    ): ResultInterface {
        $sql     = $this->getSql()->setTable($tableName ?? $this->getTableName());
        $update  = $sql->update();
        $rowData = $this->entityToArray($entity, $hydrator);
        $update->set($rowData)->where($where);
        $statement = $sql->prepareStatementForSqlObject($update);
        return $statement->execute();
    }

    protected function innerDelete(
        string|array|Closure $where,
        ?string $tableName = null
    ): ResultInterface {
        $sql    = $this->getSql()->setTable($tableName ?? $this->getTableName());
        $delete = $sql->delete();
        $delete->where($where);
        $statement = $sql->prepareStatementForSqlObject($delete);
        return $statement->execute();
    }

    protected function getSql(): Sql
    {
        $this->sql = new Sql($this->dbAdapter);
        return $this->sql;
    }

    protected function getSelect(?string $tableName = null): Select
    {
        return $this->getSql()->select($tableName ?? $this->getTableName());
    }

    public function getTableName(): string
    {
        return $this->tableName ?? 'user';
    }

    protected function entityToArray(
        UserInterface $entity,
        ?HydratorInterface $hydrator
    ): array {
        if ($hydrator === null) {
            $hydrator = $this->getHydrator();
        }
        return $hydrator->extract($entity);
    }

    public function getEntityPrototype(): UserInterface
    {
        return $this->entityPrototype;
    }

    public function getHydrator(): HydratorInterface
    {
        return $this->hydrator;
    }
}
