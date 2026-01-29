<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp;

use PhpSoftBox\CliApp\Command\CommandDefinition;
use PhpSoftBox\CliApp\Command\CommandRegistryInterface;
use PhpSoftBox\CliApp\Command\HandlerInterface;
use PhpSoftBox\CliApp\Events\EventDispatcherInterface;
use PhpSoftBox\CliApp\Events\NullEventDispatcher;
use PhpSoftBox\CliApp\Io\IoInterface;
use PhpSoftBox\CliApp\Runner\DefaultErrorHandler;
use PhpSoftBox\CliApp\Runner\ErrorHandlerInterface;
use PhpSoftBox\CliApp\Runner\Runner;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Throwable;

use function class_exists;
use function is_array;
use function is_callable;
use function is_string;

final class CliApp implements CliAppInterface
{
    public function __construct(
        private readonly CommandRegistryInterface $registry,
        private readonly IoInterface $io,
        private readonly ?ContainerInterface $container = null,
        private readonly EventDispatcherInterface $events = new NullEventDispatcher(),
        private readonly ErrorHandlerInterface $errorHandler = new DefaultErrorHandler(),
    ) {
    }

    public function runCommand(string $command, array $argv): Response
    {
        return $this->runner()->run($command, $argv);
    }

    public function runner(
        ?IoInterface $io = null,
        ?EventDispatcherInterface $events = null,
        ?ErrorHandlerInterface $errorHandler = null,
    ): Runner {
        return new Runner(
            app: $this,
            io: $io ?? $this->io,
            events: $events ?? $this->events,
            errorHandler: $errorHandler ?? $this->errorHandler,
        );
    }

    public function resolveCommand(string $name): ?CommandDefinition
    {
        return $this->registry->get($name);
    }

    public function resolveHandler(CommandDefinition $definition): mixed
    {
        $handler = $definition->handler;

        if ($handler instanceof HandlerInterface) {
            return $handler;
        }

        if (is_string($handler) && class_exists($handler)) {
            return $this->resolveClass($handler);
        }

        if (is_array($handler) && is_string($handler[0] ?? null) && class_exists($handler[0])) {
            $handler[0] = $this->resolveClass($handler[0]);

            return $handler;
        }

        if (is_callable($handler)) {
            return $handler;
        }

        throw new RuntimeException('Invalid command handler for ' . $definition->name);
    }

    private function resolveClass(string $class): object
    {
        if ($this->container) {
            try {
                return $this->container->get($class);
            } catch (Throwable) {
            }
        }

        return new $class();
    }

    public function registry(): CommandRegistryInterface
    {
        return $this->registry;
    }

    public function io(): IoInterface
    {
        return $this->io;
    }

    public function container(): ?ContainerInterface
    {
        return $this->container;
    }
}
