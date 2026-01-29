<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Command;

use PhpSoftBox\CliApp\Response;
use PhpSoftBox\CliApp\Runner\RunnerInterface;

/**
 * Контракт обработчиков команд в виде классов.
 */
interface HandlerInterface
{
    /** Выполнить команду и вернуть код/Response. */
    public function run(RunnerInterface $runner): int|Response;
}
