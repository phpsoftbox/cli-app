<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests;

use PhpSoftBox\CliApp\CliApp;
use PhpSoftBox\CliApp\Command\Command;
use PhpSoftBox\CliApp\Command\InMemoryCommandRegistry;
use PhpSoftBox\CliApp\Events\EventDispatcher;
use PhpSoftBox\CliApp\Events\Events;
use PhpSoftBox\CliApp\Io\NullIo;
use PhpSoftBox\CliApp\Response;
use PhpSoftBox\CliApp\Runner\RunnerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CliApp::class)]
#[CoversClass(EventDispatcher::class)]
#[CoversClass(Events::class)]
#[CoversClass(InMemoryCommandRegistry::class)]
#[CoversClass(Command::class)]
final class CliAppEventsTest extends TestCase
{
    /**
     * Проверяет, что события before/after/error отправляются при выполнении.
     */
    #[Test]
    public function testDispatchesBeforeAfterAndError(): void
    {
        $registry = new InMemoryCommandRegistry();
        $events   = new EventDispatcher();
        $io       = new NullIo();

        $registry->register(Command::define(
            'ok',
            'Ok command',
            [],
            fn (RunnerInterface $runner) => new Response(Response::SUCCESS, 'done'),
        ));

        $seen = [
            'before' => 0,
            'after'  => 0,
            'error'  => 0,
        ];

        $events->subscribe(Events::BEFORE_RUN, function () use (&$seen) {
            $seen['before']++;
        });
        $events->subscribe(Events::AFTER_RUN, function () use (&$seen) {
            $seen['after']++;
        });
        $events->subscribe(Events::ERROR, function () use (&$seen) {
            $seen['error']++;
        });

        $app = new CliApp($registry, $io, null, $events);

        $app->runCommand('ok', []);
        $app->runCommand('missing', []);

        self::assertSame(1, $seen['before']);
        self::assertSame(1, $seen['after']);
        self::assertSame(1, $seen['error']);
    }
}
