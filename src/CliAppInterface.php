<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp;

use PhpSoftBox\CliApp\Command\CommandRegistryInterface;
use PhpSoftBox\CliApp\Events\EventDispatcherInterface;
use PhpSoftBox\CliApp\Io\IoInterface;
use PhpSoftBox\CliApp\Runner\ErrorHandlerInterface;
use PhpSoftBox\CliApp\Runner\Runner;
use Psr\Container\ContainerInterface;

interface CliAppInterface
{
    public function runCommand(string $command, array $argv): Response;
    public function runner(?IoInterface $io = null, ?EventDispatcherInterface $events = null, ?ErrorHandlerInterface $errorHandler = null): Runner;
    public function registry(): CommandRegistryInterface;
    public function io(): IoInterface;
    public function container(): ?ContainerInterface;
}
