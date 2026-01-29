<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests\Support;

use PhpSoftBox\CliApp\Command\DaemonHandlerInterface;
use PhpSoftBox\CliApp\Command\DaemonStartupException;
use PhpSoftBox\CliApp\Response;
use PhpSoftBox\CliApp\Runner\RunnerInterface;

final class FailingDaemonHandlerClass implements DaemonHandlerInterface
{
    public function runAsDaemon(RunnerInterface $runner): void
    {
        throw new DaemonStartupException('daemon failed', Response::INVALID_INPUT);
    }
}
