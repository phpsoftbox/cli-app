<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Command;

/**
 * Простейший реестр команд в памяти.
 */
final class InMemoryCommandRegistry extends AbstractCommandRegistry
{
    public function __construct(bool $withDefaultCommands = true)
    {
        parent::__construct($withDefaultCommands);
    }
}
