<?php

declare(strict_types=1);

namespace LmcTest\User\Repository\Db\Options;

use Laminas\ServiceManager\ServiceManager;
use Lmc\User\Repository\Db\Exception\ServiceNotCreatedException;
use Lmc\User\Repository\Db\Options\Options;
use Lmc\User\Repository\Db\Options\OptionsFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;

#[CoversClass(OptionsFactory::class)]
final class OptionsFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function testFactory(): void
    {
        $serviceManager = new ServiceManager([
            'services' => [
                'config' => [
                    'lmc_user' => [],
                ],
            ],
        ]);
        $factory        = new OptionsFactory();
        $this->assertInstanceOf(Options::class, $factory($serviceManager, ''));
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testFactoryNoConfig(): void
    {
        $serviceManager = new ServiceManager([
            'services' => [
                'config' => [],
            ],
        ]);
        $factory        = new OptionsFactory();
        $this->expectException(ServiceNotCreatedException::class);
        $factory($serviceManager, '');
    }
}
