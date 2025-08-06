<?php

declare(strict_types=1);

namespace LmcTest\User\Repository\Db\Adapter;

use Laminas\Hydrator\HydratorInterface;
use Laminas\ServiceManager\ServiceManager;
use Lmc\User\Repository\Db\Adapter\BaseUserHydratorFactory;
use Lmc\User\Repository\Db\ConfigProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BaseUserHydratorFactory::class)]
final class BaseHydratorFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $configProvider = new ConfigProvider();
        $container      = new ServiceManager($configProvider->getDependencies());
        $container->setService(
            'config',
            [
                'lmc_user' => [],
            ]
        );
        $this->assertInstanceOf(HydratorInterface::class, $container->get('lmcuser_default_hydrator'));
    }
}
