<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests\Support;

use PhpSoftBox\CliApp\Command\Command;
use PhpSoftBox\CliApp\Command\CommandRegistryInterface;
use PhpSoftBox\CliApp\Loader\CommandProviderInterface;

final class ProviderLow implements CommandProviderInterface
{
    public function register(CommandRegistryInterface $registry): void
    {
        $registry->register(Command::define(
            name: 'dup:cmd',
            description: 'low',
            signature: [],
            handler: fn () => 0,
        ));
    }
}
