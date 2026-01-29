<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Runner;

use PhpSoftBox\CliApp\Command\CommandDefinition;
use PhpSoftBox\CliApp\Request\Request;
use PhpSoftBox\CliApp\Response;

interface ErrorHandlerInterface
{
    public function invalidInput(CommandDefinition $definition, Request $request, RunnerInterface $runner): Response;
    public function unknownCommand(string $command, RunnerInterface $runner): Response;
    public function showHelp(CommandDefinition $definition, RunnerInterface $runner): Response;
    public function environmentNotAllowed(CommandDefinition $definition, string $env, RunnerInterface $runner): Response;
}
