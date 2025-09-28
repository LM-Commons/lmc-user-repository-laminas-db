<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Db\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class ServiceNotCreatedException extends RuntimeException implements ContainerExceptionInterface
{
}
