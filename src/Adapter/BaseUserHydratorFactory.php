<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Db\Adapter;

use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Hydrator\Strategy\ExplodeStrategy;
use Lmc\User\Repository\Db\Options\Options;
use Psr\Container\ContainerInterface;

use function assert;

final class BaseUserHydratorFactory
{
    public function __invoke(ContainerInterface $container): HydratorInterface
    {
        $hydrator = new ClassMethodsHydrator();
        /** @var Options $commonOptions */
        $commonOptions = $container->get(Options::class);
        $delimiter     = $commonOptions->getRolesDelimiter();
        assert(! empty($delimiter));
        $hydrator->addStrategy('roles', new ExplodeStrategy($delimiter));
        return $hydrator;
    }
}
