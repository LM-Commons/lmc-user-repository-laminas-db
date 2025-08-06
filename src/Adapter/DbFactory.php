<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Db\Adapter;

use Laminas\Db\Adapter\Adapter;
use Laminas\Hydrator\HydratorInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Lmc\User\Repository\Db\Options\Options;
use Psr\Container\ContainerInterface;

use function gettype;
use function is_object;
use function sprintf;

final class DbFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Db
    {
        /** @var Options $options */
        $options   = $container->get(Options::class);
        $dbAdapter = $container->get('lmcuser_laminas_db_adapter');
        if (! $dbAdapter instanceof Adapter) {
            throw new ServiceNotCreatedException(
                sprintf(
                    "'lmcuser_laminas_db_adapter' does not resolve is not a valid database adapter; received '%s'",
                    is_object($dbAdapter) ? $dbAdapter::class : gettype($dbAdapter)
                )
            );
        }

        $hydrator = $container->get('lmcuser_user_hydrator');
        if (! $hydrator instanceof HydratorInterface) {
            throw new ServiceNotCreatedException(
                sprintf(
                    "'lmcuser_user_hydrator' does not resolve is not a valid hydrator; received '%s'",
                    is_object($hydrator) ? $hydrator::class : gettype($hydrator)
                )
            );
        }

        $entityClass = $options->getUserEntityClass();

        /**
         * @psalm-suppress InvalidStringClass
         * @psalm-suppress ArgumentTypeCoercion
         */
        return new Db(
            $dbAdapter,
            $hydrator,
            new $entityClass(),
            $options->getTableName(),
            $options->getRolesDelimiter(),
        );
    }
}
