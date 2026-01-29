<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests;

use PhpSoftBox\CliApp\CliApp;
use PhpSoftBox\CliApp\Command\ArgumentDefinition;
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
#[CoversClass(ArgumentDefinition::class)]
final class RunnerSubCommandTest extends TestCase
{
    /**
     * Проверяет, что runSubCommand не перезаписывает request родительской команды.
     */
    #[Test]
    public function testRunSubCommandDoesNotOverwriteParentRequest(): void
    {
        $registry = new InMemoryCommandRegistry(withDefaultCommands: false);
        $io       = new NullIo();

        $app = new CliApp($registry, $io);

        $captured = [];

        $registry->register(Command::define(
            name: 'child',
            description: 'Child command',
            signature: [new ArgumentDefinition('childArg')],
            handler: function (RunnerInterface $runner) use (&$captured) {
                $captured['child'] = $runner->request()->param('childArg');

                return Response::SUCCESS;
            },
        ));

        $registry->register(Command::define(
            name: 'parent',
            description: 'Parent command',
            signature: [new ArgumentDefinition('parentArg')],
            handler: function (RunnerInterface $runner) use (&$captured) {
                $captured['before'] = $runner->request()->param('parentArg');
                $runner->runSubCommand('child', ['child']);
                $captured['after'] = $runner->request()->param('parentArg');

                return Response::SUCCESS;
            },
        ));

        $app->runner()->run('parent', ['parent']);

        self::assertSame('parent', $captured['before']);
        self::assertSame('child', $captured['child']);
        self::assertSame('parent', $captured['after']);
    }
}
