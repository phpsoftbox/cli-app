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
}
