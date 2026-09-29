<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests;

use PhpSoftBox\CliApp\CliApp;
use PhpSoftBox\CliApp\Command\Command;
use PhpSoftBox\CliApp\Command\InMemoryCommandRegistry;
use PhpSoftBox\CliApp\Io\NullIo;
use PhpSoftBox\CliApp\Response;
use PhpSoftBox\CliApp\Runner\Runner;
use PhpSoftBox\CliApp\Runner\RunnerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CliApp::class)]
#[CoversClass(Runner::class)]
#[CoversClass(InMemoryCommandRegistry::class)]
#[CoversClass(Command::class)]
final class EnvironmentRestrictionTest extends TestCase
{
    /**
     * Проверяет блокировку команды по окружению.
     */
    #[Test]
    public function testCommandBlockedByEnvironment(): void
    {
        $registry = new InMemoryCommandRegistry(withDefaultCommands: false);

        $app    = new CliApp($registry, new NullIo());
        $called = false;

        $registry->register(Command::define(
            name: 'secure',
            description: 'Secure command',
            signature: [],
            handler: function (RunnerInterface $runner) use (&$called) {
                $called = true;

                return Response::SUCCESS;
            },
            environments: ['production'],
        ));

        $resp = $app->runner()->run('secure', ['--environment', 'local']);

        self::assertSame(Response::FAILURE, $resp->code);
        self::assertFalse($called);
    }

    /**
     * Проверим, что без `--environment` окружение берётся из резолвера приложения: команда только для `prod`
     * выполняется в приложении, работающем в `prod`.
     *
     * @see RunnerInterface::environment()
     */
    #[Test]
    public function usesApplicationEnvironmentByDefault(): void
    {
        $registry = new InMemoryCommandRegistry(withDefaultCommands: false);

        $app  = new CliApp($registry, new NullIo(), environmentResolver: static fn (): string => 'prod');
        $seen = null;

        $registry->register(Command::define(
            name: 'deploy',
            description: 'Prod only',
            signature: [],
            handler: function (RunnerInterface $runner) use (&$seen) {
                $seen = $runner->environment();

                return Response::SUCCESS;
            },
            environments: ['prod'],
        ));

        self::assertSame(Response::SUCCESS, $app->runner()->run('deploy', [])->code);
        self::assertSame('prod', $seen);
    }

    /**
     * Проверим, что `--environment` имеет приоритет над окружением приложения.
     *
     * @see RunnerInterface::environment()
     */
    #[Test]
    public function optionOverridesApplicationEnvironment(): void
    {
        $registry = new InMemoryCommandRegistry(withDefaultCommands: false);

        $app  = new CliApp($registry, new NullIo(), environmentResolver: static fn (): string => 'prod');
        $seen = null;

        $registry->register(Command::define(
            name: 'show',
            description: 'Show env',
            signature: [],
            handler: function (RunnerInterface $runner) use (&$seen) {
                $seen = $runner->environment();

                return Response::SUCCESS;
            },
        ));

        $app->runner()->run('show', ['-e', 'test']);

        self::assertSame('test', $seen);
    }
}
