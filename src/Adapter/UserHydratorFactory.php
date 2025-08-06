<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Db\Adapter;

use Laminas\Hydrator\HydratorInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function sprintf;

final class UserHydratorFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): UserHydrator
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
