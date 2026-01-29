<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Runner;

use PhpSoftBox\CliApp\Request\Request;
use PhpSoftBox\CliApp\Response;
use PhpSoftBox\CliApp\Io\IoInterface;

interface RunnerInterface
{
    public function run(string $command, array $argv): Response;
    public function runSubCommand(string $command, array $argv): Response;
    public function request(): Request;
    public function io(): IoInterface;
}
