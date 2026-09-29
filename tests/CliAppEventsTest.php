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
use PhpSoftBox\CliApp\Runner\Runner;
use PhpSoftBox\CliApp\Runner\RunnerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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

    /**
     * Проверим, что исключение обработчика отправляет событие ERROR и пробрасывается дальше.
     *
     * @see Runner::run()
     */
    #[Test]
    public function dispatchesErrorOnHandlerException(): void
    {
        $registry = new InMemoryCommandRegistry(withDefaultCommands: false);
        $events   = new EventDispatcher();
        $errors   = [];

        $registry->register(Command::define('boom', 'Fails', [], static function (): never {
            throw new RuntimeException('Handler failed.');
        }));
        $events->subscribe(Events::ERROR, function (array $payload) use (&$errors): void {
            $errors[] = $payload['exception'] ?? null;
        });

        try {
            new CliApp($registry, new NullIo(), null, $events)->runCommand('boom', []);
            self::fail('Exception expected.');
        } catch (RuntimeException) {
            // ожидаемо
        }

        self::assertCount(1, $errors);
        self::assertInstanceOf(RuntimeException::class, $errors[0]);
    }
}
