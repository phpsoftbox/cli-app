<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests\Support;

use PhpSoftBox\CliApp\Command\HandlerInterface;
use PhpSoftBox\CliApp\Response;
use PhpSoftBox\CliApp\Runner\RunnerInterface;

final class DependentHandler implements HandlerInterface
{
    public function __construct(
        private readonly string $dependency,
    ) {
    }

    public function run(RunnerInterface $runner): int|Response
    {
        return $this->dependency !== '' ? Response::SUCCESS : Response::FAILURE;
    }
}
