<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests;

use PhpSoftBox\CliApp\Command\AbstractCommandRegistry;
use PhpSoftBox\CliApp\Command\InMemoryCommandRegistry;
use PhpSoftBox\CliApp\Command\OptionDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_map;

#[CoversClass(InMemoryCommandRegistry::class)]
#[CoversClass(AbstractCommandRegistry::class)]
#[CoversClass(OptionDefinition::class)]
final class DefaultCommandRegistryTest extends TestCase
{
    /**
     * Проверяет, что команда list регистрируется по умолчанию.
     */
    #[Test]
    public function testListCommandRegisteredByDefault(): void
    {
        $registry = new InMemoryCommandRegistry();

        self::assertNotNull($registry->get('list'));
    }

    /**
     * Проверяет, что дефолтные команды можно отключить.
     */
    #[Test]
    public function testListCommandCanBeDisabled(): void
    {
        $registry = new InMemoryCommandRegistry(withDefaultCommands: false);

        self::assertNull($registry->get('list'));
    }

    /**
     * Проверяет, что глобальные опции доступны в реестре.
     */
    #[Test]
    public function testGlobalOptionsProvided(): void
    {
        $registry = new InMemoryCommandRegistry();

        $options = $registry->globalOptions();

        $names = array_map(fn ($o) => $o->name, $options);
        self::assertContains('environment', $names);
        self::assertContains('help', $names);
    }
}
