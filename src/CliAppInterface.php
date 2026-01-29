<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp;

use PhpSoftBox\CliApp\Command\CommandRegistryInterface;
use PhpSoftBox\CliApp\Io\IoInterface;
use PhpSoftBox\CliApp\Runner\Runner;
use Psr\Container\ContainerInterface;

interface CliAppInterface
{
    public function runCommand(string $command, array $argv): Response;
    public function runner(?IoInterface $io = null, ?\PhpSoftBox\CliApp\Events\EventDispatcherInterface $events = null, ?\PhpSoftBox\CliApp\Runner\ErrorHandlerInterface $errorHandler = null): Runner;
    public function registry(): CommandRegistryInterface;
    public function io(): IoInterface;
    public function container(): ?ContainerInterface;
}
