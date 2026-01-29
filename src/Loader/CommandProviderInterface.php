<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Loader;

use PhpSoftBox\CliApp\Command\CommandRegistryInterface;

/**
 * Провайдер команд для autodiscovery через composer extra.
 */
interface CommandProviderInterface
{
    public function register(CommandRegistryInterface $registry): void;
}
