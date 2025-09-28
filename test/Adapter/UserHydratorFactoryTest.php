<?php

declare(strict_types=1);

namespace LmcTest\User\Repository\Db\Adapter;

use Laminas\ServiceManager\ServiceManager;
use Lmc\User\Repository\Db\Adapter\UserHydrator;
use Lmc\User\Repository\Db\Adapter\UserHydratorFactory;
use Lmc\User\Repository\Db\ConfigProvider;
use Lmc\User\Repository\Db\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use stdClass;

#[CoversClass(UserHydratorFactory::class)]
final class UserHydratorFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function testInvoke(): void
    {
        $configProvider = new ConfigProvider();
        $container      = new ServiceManager($configProvider->getDependencies());
        $container->setService('config', ['lmc_user' => []]);
        $this->assertInstanceOf(UserHydrator::class, $container->get(UserHydrator::class));
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testInvalidBaseHydrator(): void
    {
        $container = new ServiceManager([
            'services' => [
                'lmcuser_base_hydrator' => new stdClass(),
            ],
        ]);
        $factory   = new UserHydratorFactory();
        $this->expectException(ServiceNotCreatedException::class);
        $factory($container, '');
    }
}
