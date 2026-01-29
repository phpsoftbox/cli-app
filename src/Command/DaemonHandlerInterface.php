<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Command;

use PhpSoftBox\CliApp\Runner\RunnerInterface;

/**
 * Контракт обработчиков daemon-команд.
 */
interface DaemonHandlerInterface
{
    /** Выполнить команду как long-running процесс. */
    public function runAsDaemon(RunnerInterface $runner): void;
}
