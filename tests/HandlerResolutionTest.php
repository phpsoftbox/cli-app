<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests;

use PhpSoftBox\CliApp\CliApp;
use PhpSoftBox\CliApp\Command\Command;
use PhpSoftBox\CliApp\Command\InMemoryCommandRegistry;
use PhpSoftBox\CliApp\Io\NullIo;
use PhpSoftBox\CliApp\Response;
use PhpSoftBox\CliApp\Runner\Runner;
use PhpSoftBox\CliApp\Tests\Support\HandlerClass;
use PhpSoftBox\CliApp\Tests\Support\InvokableClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

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
}
