<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests\Support;

use PhpSoftBox\CliApp\Command\DaemonHandlerInterface;
use PhpSoftBox\CliApp\Runner\RunnerInterface;

final class DaemonHandlerClass implements DaemonHandlerInterface
{
    public static bool $called = false;

    public function runAsDaemon(RunnerInterface $runner): void
    {
        self::$called = true;
    }
}
