<?php

declare(strict_types=1);

namespace Lmc\User\Repository\test;

use Lmc\User\Repository\Db\ConfigProvider;
use PHPUnit\Framework\TestCase;

final class ConfigProviderTest extends TestCase
{
    public function testConfigProvider(): void
    {
        $configProvider = new ConfigProvider();
        $this->assertIsArray($configProvider());
    }
}
