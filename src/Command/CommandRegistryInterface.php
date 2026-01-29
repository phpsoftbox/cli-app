<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Command;

/**
 * Реестр доступных команд.
 */
interface CommandRegistryInterface
{
    public function register(CommandDefinition $definition): void;

    public function get(string $name): ?CommandDefinition;

    /** @return list<CommandDefinition> */
    public function all(): array;
}
