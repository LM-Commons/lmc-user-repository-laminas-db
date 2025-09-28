<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Db\Adapter;

use Laminas\Hydrator\HydratorInterface;
use Lmc\User\Repository\Db\Exception\ServiceNotCreatedException;
use Psr\Container\ContainerInterface;

use function sprintf;

final class UserHydratorFactory
{
    public function __invoke(ContainerInterface $container): UserHydrator
    {
        /** @var HydratorInterface $baseHydrator */
        $baseHydrator = $container->get('lmcuser_base_hydrator');
        if (! $baseHydrator instanceof HydratorInterface) {
            throw new ServiceNotCreatedException(
                sprintf(
                    "'lmcuser_base_hydrator' must be an instance of '%s\HydratorInterface'",
                    HydratorInterface::class
                )
            );
        }
        return new UserHydrator($baseHydrator);
    }
}
