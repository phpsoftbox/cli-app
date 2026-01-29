<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests;

use PhpSoftBox\CliApp\Command\ArgumentDefinition;
use PhpSoftBox\CliApp\Command\Command;
use PhpSoftBox\CliApp\Command\OptionDefinition;
use PhpSoftBox\CliApp\Command\UsageFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UsageFormatter::class)]
#[CoversClass(Command::class)]
#[CoversClass(ArgumentDefinition::class)]
#[CoversClass(OptionDefinition::class)]
final class UsageFormatterTest extends TestCase
{
    /**
     * Проверяет форматирование help: usage, аргументы, опции, алиасы и типы.
     */
    #[Test]
    public function testFormatsHelpWithArgsAndOptions(): void
    {
        $definition = Command::define(
            name: 'demo:run',
            description: 'Demo command',
            signature: [
                new ArgumentDefinition('path', 'Path to file', required: false, default: '/tmp'),
                new OptionDefinition('force', 'f', 'Force', flag: true),
                new OptionDefinition('count', 'c', 'Count', required: true, default: 1, type: 'int'),
            ],
            handler: fn () => 0,
            aliases: ['demo'],
            environments: ['local'],
        );

        $formatter = new UsageFormatter();

        $help = $formatter->formatHelp($definition);

        self::assertStringContainsString('Usage: demo:run', $help);
        self::assertStringContainsString('Arguments:', $help);
        self::assertStringContainsString('Options:', $help);
        self::assertStringContainsString('Aliases: demo', $help);
        self::assertStringContainsString('--force', $help);
        self::assertStringContainsString('--count', $help);
        self::assertStringContainsString('default: /tmp', $help);
        self::assertStringContainsString('type: int', $help);
        self::assertStringContainsString('demo', $help);
    }
}
