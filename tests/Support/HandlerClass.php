<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests\Support;

use PhpSoftBox\CliApp\Command\HandlerInterface;
use PhpSoftBox\CliApp\Response;
use PhpSoftBox\CliApp\Runner\RunnerInterface;

final class HandlerClass implements HandlerInterface
{
    public static bool $called = false;

    public function run(RunnerInterface $runner): int|Response
    {
        self::$called = true;

        return Response::SUCCESS;
    }
}
