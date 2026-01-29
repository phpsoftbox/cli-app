<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests;

use PhpSoftBox\CliApp\Config\CliAppConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CliAppConfig::class)]
final class CliAppConfigTest extends TestCase
{
    /**
     * Проверяет преобразование массива настроек в CliAppConfig.
     */
    #[Test]
    public function testFromArrayResolvesPaths(): void
    {
        $config = CliAppConfig::fromArray([
            'bootstrap'        => 'bootstrap/cli.php',
            'commandPaths'     => ['console', 'commands'],
            'commandFiles'     => ['vendor/pkg/cli.php'],
            'commandProviders' => [
                'Vendor\\Pkg\\Provider',
                ['class' => 'Vendor\\Pkg\\Provider2', 'priority' => 10],
            ],
            'withDefaultCommands' => false,
        ], '/app');

        self::assertSame('/app/bootstrap/cli.php', $config->bootstrapFile);
        self::assertSame(['/app/console', '/app/commands'], $config->commandPaths);
        self::assertSame(['/app/vendor/pkg/cli.php'], $config->commandFiles);
        self::assertSame([
            ['class' => 'Vendor\\Pkg\\Provider', 'priority' => 0],
            ['class' => 'Vendor\\Pkg\\Provider2', 'priority' => 10],
        ], $config->commandProviders);
        self::assertFalse($config->withDefaultCommands);
    }
}
