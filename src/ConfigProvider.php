<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Db;

use Laminas\Db\Adapter\Adapter;
use Lmc\User\Repository\Db\Adapter\BaseUserHydratorFactory;
use Lmc\User\Repository\Db\Adapter\DbFactory;
use Lmc\User\Repository\Db\Adapter\UserHydrator;
use Lmc\User\Repository\Db\Adapter\UserHydratorFactory;
use Lmc\User\Repository\Db\Options\Options;
use Lmc\User\Repository\Db\Options\OptionsFactory;
use Lmc\User\Repository\UserInterface;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'aliases'   => [
                'lmcuser_laminas_db_adapter' => Adapter::class,
                'lmcuser_user_adapter'       => UserInterface::class,
                'lmcuser_user_hydrator'      => UserHydrator::class,
                'lmcuser_base_hydrator'      => 'lmcuser_default_hydrator',
            ],
            'factories' => [
                'lmcuser_default_hydrator' => BaseUserHydratorFactory::class,
                Options::class             => OptionsFactory::class,
                UserInterface::class       => DbFactory::class,
                UserHydrator::class        => UserHydratorFactory::class,
            ],
        ];
    }
}
