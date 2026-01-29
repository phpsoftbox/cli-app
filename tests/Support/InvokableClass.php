<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests\Support;

use PhpSoftBox\CliApp\Response;
use PhpSoftBox\CliApp\Runner\RunnerInterface;

final class InvokableClass
{
    public static bool $called = false;

    public function __invoke(RunnerInterface $runner): int
    {
        self::$called = true;

        return Response::SUCCESS;
    }
}
