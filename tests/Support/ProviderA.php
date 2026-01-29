<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests\Support;

use PhpSoftBox\CliApp\Command\Command;
use PhpSoftBox\CliApp\Command\CommandRegistryInterface;
use PhpSoftBox\CliApp\Loader\CommandProviderInterface;

final class ProviderA implements CommandProviderInterface
{
    public static bool $registered = false;

    public function register(CommandRegistryInterface $registry): void
    {
        self::$registered = true;
        $registry->register(Command::define(
            name: 'a:cmd',
            description: 'A',
            signature: [],
            handler: fn () => 0,
        ));
    }
}
