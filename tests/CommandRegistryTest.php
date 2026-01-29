<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests;

use PhpSoftBox\CliApp\Command\AbstractCommandRegistry;
use PhpSoftBox\CliApp\Command\Command;
use PhpSoftBox\CliApp\Command\InMemoryCommandRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InMemoryCommandRegistry::class)]
#[CoversClass(AbstractCommandRegistry::class)]
#[CoversClass(Command::class)]
final class CommandRegistryTest extends TestCase
{
    /**
     * Проверяет, что алиасы указывают на одно и то же определение команды.
     */
    #[Test]
    public function testAliasResolvesToSameDefinition(): void
    {
        $registry = new InMemoryCommandRegistry(withDefaultCommands: false);

        $registry->register(Command::define(
            name: 'alpha',
            description: 'Alpha command',
            signature: [],
            handler: fn () => 0,
            aliases: ['a', 'al'],
        ));

        $original = $registry->get('alpha');
        self::assertNotNull($original);
        self::assertSame($original, $registry->get('a'));
        self::assertSame($original, $registry->get('al'));
    }
}
