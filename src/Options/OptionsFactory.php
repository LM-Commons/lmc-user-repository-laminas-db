<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Db\Options;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function assert;
use function is_array;

final class OptionsFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): Options
    {
        $config = $container->get('config');
        assert(is_array($config));

        if (isset($config['lmc_user']) && is_array($config['lmc_user'])) {
            $config = $config['lmc_user'];
        } else {
            throw new ServiceNotCreatedException("Cannot find a configuration for 'lmc_user'");
        }

        return new Options($config);
    }
}
