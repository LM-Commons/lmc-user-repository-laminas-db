<?php

declare(strict_types=1);

namespace LmcTest\User\Repository\Db\Adapter;

use Laminas\Db\Adapter\Adapter;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceManager;
use Lmc\User\Repository\AdapterInterface;
use Lmc\User\Repository\Db\Adapter\DbFactory;
use Lmc\User\Repository\Db\ConfigProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use stdClass;

#[CoversClass(DbFactory::class)]
final class DbFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface|Exception
     */
    public function testInvoke(): void
    {
        $configProvider = new ConfigProvider();
        $container      = new ServiceManager($configProvider->getDependencies());
        $container->setService('config', [
            'lmc_user' => [],
        ]);
        $container->setService(Adapter::class, $this->createMock(Adapter::class));
        $factory = new DbFactory();
        $this->assertInstanceOf(AdapterInterface::class, $factory($container, ''));
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testInvalidDbAdapter(): void
    {
        $configProvider = new ConfigProvider();
        $container      = new ServiceManager($configProvider->getDependencies());
        $container->setService('config', [
            'lmc_user' => [],
        ]);
        $container->setService(Adapter::class, new stdClass());
        $this->expectException(ServiceNotCreatedException::class);
        $factory = new DbFactory();
        $factory($container, '');
    }

    /**
     * @throws ContainerExceptionInterface|Exception
     */
    public function testInvalidHydrator(): void
    {
        $configProvider = new ConfigProvider();
        $container      = new ServiceManager($configProvider->getDependencies());
        $container->setService('config', [
            'lmc_user' => [],
        ]);
        $container->setService('lmcuser_user_hydrator', new stdClass());
        $container->setService(Adapter::class, $this->createMock(Adapter::class));
        $this->expectException(ServiceNotCreatedException::class);
        $factory = new DbFactory();
        $factory($container, '');
    }
}
