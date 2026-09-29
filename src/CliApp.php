<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp;

use Closure;
use PhpSoftBox\CliApp\Command\CommandDefinition;
use PhpSoftBox\CliApp\Command\CommandRegistryInterface;
use PhpSoftBox\CliApp\Command\DaemonHandlerInterface;
use PhpSoftBox\CliApp\Command\HandlerInterface;
use PhpSoftBox\CliApp\Events\EventDispatcherInterface;
use PhpSoftBox\CliApp\Events\NullEventDispatcher;
use PhpSoftBox\CliApp\Io\IoInterface;
use PhpSoftBox\CliApp\Runner\DefaultErrorHandler;
use PhpSoftBox\CliApp\Runner\ErrorHandlerInterface;
use PhpSoftBox\CliApp\Runner\Runner;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Throwable;

use function class_exists;
use function getenv;
use function is_array;
use function is_callable;
use function is_string;

final class CliApp implements CliAppInterface
{
    private readonly ?Closure $environmentResolver;

    /**
     * @param (callable(): string)|null $environmentResolver окружение приложения (например, `Environment::detect()`
     *                                                       скелета): по нему проверяются `environments` команд и
     *                                                       чистятся кеши, если `--environment` не передан
     */
    public function __construct(
        private readonly CommandRegistryInterface $registry,
        private readonly IoInterface $io,
        private readonly ?ContainerInterface $container = null,
        private readonly EventDispatcherInterface $events = new NullEventDispatcher(),
        private readonly ErrorHandlerInterface $errorHandler = new DefaultErrorHandler(),
        ?callable $environmentResolver = null,
    ) {
        $this->environmentResolver = $environmentResolver === null ? null : Closure::fromCallable($environmentResolver);
    }

    /**
     * Окружение приложения: из резолвера, иначе `APP_ENV` процесса, иначе `dev`.
     */
    public function environment(): string
    {
        if ($this->environmentResolver !== null) {
            return ($this->environmentResolver)();
        }

        foreach ([$_ENV['APP_ENV'] ?? null, $_SERVER['APP_ENV'] ?? null, getenv('APP_ENV')] as $value) {
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return 'dev';
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

        if ($handler instanceof HandlerInterface || $handler instanceof DaemonHandlerInterface) {
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
            } catch (NotFoundExceptionInterface $exception) {
                // Не найдена вложенная зависимость, а не сам обработчик: сообщаем её, а не «нужен контейнер».
                if ($this->container->has($class)) {
                    throw new RuntimeException(
                        'Failed to resolve class "' . $class . '" from container: ' . $exception->getMessage(),
                        0,
                        $exception,
                    );
                }
            } catch (Throwable $exception) {
                throw new RuntimeException(
                    'Failed to resolve class "' . $class . '" from container: ' . $exception->getMessage(),
                    0,
                    $exception,
                );
            }
        }

        return $this->instantiateWithoutContainer($class);
    }

    private function instantiateWithoutContainer(string $class): object
    {
        try {
            $reflection = new ReflectionClass($class);

            $constructor = $reflection->getConstructor();
            if ($constructor !== null && $constructor->getNumberOfRequiredParameters() > 0) {
                throw new RuntimeException(
                    'Cannot instantiate class "' . $class . '" without container: constructor has required dependencies.',
                );
            }
        } catch (ReflectionException $exception) {
            throw new RuntimeException('Cannot instantiate class "' . $class . '".', 0, $exception);
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
