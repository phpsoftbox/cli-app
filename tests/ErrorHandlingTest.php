<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests;

use PhpSoftBox\CliApp\CliApp;
use PhpSoftBox\CliApp\Command\ArgumentDefinition;
use PhpSoftBox\CliApp\Command\Command;
use PhpSoftBox\CliApp\Command\InMemoryCommandRegistry;
use PhpSoftBox\CliApp\Response;
use PhpSoftBox\CliApp\Runner\DefaultErrorHandler;
use PhpSoftBox\CliApp\Runner\Runner;
use PhpSoftBox\CliApp\Tests\Support\BufferIo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_column;
use function implode;

#[CoversClass(CliApp::class)]
#[CoversClass(Runner::class)]
#[CoversClass(DefaultErrorHandler::class)]
#[CoversClass(InMemoryCommandRegistry::class)]
#[CoversClass(Command::class)]
#[CoversClass(ArgumentDefinition::class)]
final class ErrorHandlingTest extends TestCase
{
    /**
     * Проверяет сообщение об ошибке для неизвестной команды.
     */
    #[Test]
    public function testUnknownCommandWritesError(): void
    {
        $io       = new BufferIo();
        $registry = new InMemoryCommandRegistry(withDefaultCommands: false);

        $app = new CliApp($registry, $io);

        $resp = $app->runner()->run('missing', []);

        self::assertSame(Response::FAILURE, $resp->code);
        self::assertNotEmpty($io->lines);
        self::assertStringContainsString('не найдена', $io->lines[0]['message']);
    }

    /**
     * Проверяет, что при невалидном вводе показывается Usage.
     */
    #[Test]
    public function testInvalidInputShowsUsage(): void
    {
        $io       = new BufferIo();
        $registry = new InMemoryCommandRegistry(withDefaultCommands: false);

        $app = new CliApp($registry, $io);

        $registry->register(Command::define(
            name: 'demo',
            description: 'Demo',
            signature: [new ArgumentDefinition('arg1', 'Arg 1', required: true)],
            handler: fn () => Response::SUCCESS,
        ));

        $resp = $app->runner()->run('demo', []);

        self::assertSame(Response::INVALID_INPUT, $resp->code);
        $text = implode("\n", array_column($io->lines, 'message'));
        self::assertStringContainsString('Usage:', $text);
        self::assertStringContainsString('demo', $text);
    }

    /**
     * Проверяет, что опция --help выводит Usage.
     */
    #[Test]
    public function testHelpOptionShowsUsage(): void
    {
        $io       = new BufferIo();
        $registry = new InMemoryCommandRegistry(withDefaultCommands: false);

        $app = new CliApp($registry, $io);

        $registry->register(Command::define(
            name: 'helpme',
            description: 'Help me',
            signature: [new ArgumentDefinition('arg1', 'Arg 1', required: true)],
            handler: fn () => Response::SUCCESS,
        ));

        $resp = $app->runner()->run('helpme', ['--help']);

        self::assertSame(Response::SUCCESS, $resp->code);
        $text = implode("\n", array_column($io->lines, 'message'));
        self::assertStringContainsString('Usage:', $text);
        self::assertStringContainsString('helpme', $text);
    }
}
