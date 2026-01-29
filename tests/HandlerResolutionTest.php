<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests;

use PhpSoftBox\CliApp\CliApp;
use PhpSoftBox\CliApp\Command\Command;
use PhpSoftBox\CliApp\Command\InMemoryCommandRegistry;
use PhpSoftBox\CliApp\Io\NullIo;
use PhpSoftBox\CliApp\Response;
use PhpSoftBox\CliApp\Runner\Runner;
use PhpSoftBox\CliApp\Tests\Support\DependentHandler;
use PhpSoftBox\CliApp\Tests\Support\HandlerClass;
use PhpSoftBox\CliApp\Tests\Support\InvokableClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

#[CoversClass(CliApp::class)]
#[CoversClass(Runner::class)]
#[CoversClass(InMemoryCommandRegistry::class)]
#[CoversClass(Command::class)]
final class HandlerResolutionTest extends TestCase
{
    /**
     * Проверяет резолвинг класса, реализующего HandlerInterface.
     */
    #[Test]
    public function testResolvesHandlerInterfaceClass(): void
    {
        $registry = new InMemoryCommandRegistry(withDefaultCommands: false);

        $app = new CliApp($registry, new NullIo());

        $registry->register(Command::define(
            name: 'handler',
            description: 'Handler class',
            signature: [],
            handler: HandlerClass::class,
            environments: [],
        ));

        $resp = $app->runner()->run('handler', []);

        self::assertSame(Response::SUCCESS, $resp->code);
        self::assertTrue(HandlerClass::$called);
    }

    /**
     * Проверяет резолвинг invokable класса в качестве обработчика.
     */
    #[Test]
    public function testResolvesInvokableClass(): void
    {
        $registry = new InMemoryCommandRegistry(withDefaultCommands: false);

        $app = new CliApp($registry, new NullIo());

        $registry->register(Command::define(
            name: 'invokable',
            description: 'Invokable class',
            signature: [],
            handler: InvokableClass::class,
            environments: [],
        ));

        $resp = $app->runner()->run('invokable', []);

        self::assertSame(Response::SUCCESS, $resp->code);
        self::assertTrue(InvokableClass::$called);
    }

    /**
     * Проверяет, что при отсутствии entry в контейнере класс без зависимостей
     * корректно создаётся через fallback.
     */
    #[Test]
    public function testFallsBackToDirectInstantiationWhenContainerEntryNotFound(): void
    {
        $registry  = new InMemoryCommandRegistry(withDefaultCommands: false);
        $container = new class () implements ContainerInterface {
            public function get(string $id): mixed
            {
                throw new class () extends RuntimeException implements NotFoundExceptionInterface {
                };
            }

            public function has(string $id): bool
            {
                return false;
            }
        };

        $app = new CliApp($registry, new NullIo(), $container);
        $registry->register(Command::define(
            name: 'handler-fallback',
            description: 'Handler fallback',
            signature: [],
            handler: HandlerClass::class,
            environments: [],
        ));

        $resp = $app->runner()->run('handler-fallback', []);

        self::assertSame(Response::SUCCESS, $resp->code);
        self::assertTrue(HandlerClass::$called);
    }

    /**
     * Проверяет, что ошибки при создании entry контейнером не скрываются fallback-логикой.
     */
    #[Test]
    public function testContainerResolutionErrorIsNotSilenced(): void
    {
        $registry  = new InMemoryCommandRegistry(withDefaultCommands: false);
        $container = new class () implements ContainerInterface {
            public function get(string $id): mixed
            {
                throw new RuntimeException('broken definition');
            }

            public function has(string $id): bool
            {
                return true;
            }
        };

        $app        = new CliApp($registry, new NullIo(), $container);
        $definition = Command::define(
            name: 'dependent',
            description: 'Dependent handler',
            signature: [],
            handler: DependentHandler::class,
            environments: [],
        );

        $registry->register($definition);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to resolve class');

        $app->resolveHandler($definition);
    }

    /**
     * Проверяет, что без контейнера обработчик с обязательными зависимостями
     * получает понятную ошибку вместо ArgumentCountError.
     */
    #[Test]
    public function testDependentHandlerWithoutContainerThrowsClearException(): void
    {
        $registry = new InMemoryCommandRegistry(withDefaultCommands: false);

        $app        = new CliApp($registry, new NullIo());
        $definition = Command::define(
            name: 'dependent-no-container',
            description: 'Dependent handler without container',
            signature: [],
            handler: DependentHandler::class,
            environments: [],
        );

        $registry->register($definition);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot instantiate class');

        $app->resolveHandler($definition);
    }
}
