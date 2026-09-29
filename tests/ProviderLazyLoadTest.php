<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests;

use PhpSoftBox\CliApp\Command\AbstractCommandRegistry;
use PhpSoftBox\CliApp\Command\Command;
use PhpSoftBox\CliApp\Command\InMemoryCommandRegistry;
use PhpSoftBox\CliApp\Tests\Support\ProviderA;
use PhpSoftBox\CliApp\Tests\Support\ProviderHigh;
use PhpSoftBox\CliApp\Tests\Support\ProviderLow;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InMemoryCommandRegistry::class)]
#[CoversClass(AbstractCommandRegistry::class)]
#[CoversClass(Command::class)]
final class ProviderLazyLoadTest extends TestCase
{
    /**
     * Проверяет, что провайдер команд загружается лениво при первом get().
     */
    #[Test]
    public function testProviderLoadsOnlyOnDemand(): void
    {
        ProviderA::$registered = false;

        $registry = new InMemoryCommandRegistry(withDefaultCommands: false);

        $registry->addProvider(ProviderA::class, 1);

        self::assertFalse(ProviderA::$registered);

        $def = $registry->get('a:cmd');
        self::assertNotNull($def);
        self::assertTrue(ProviderA::$registered);
    }

    /**
     * Проверяет, что приоритет провайдера позволяет переопределить команду.
     */
    #[Test]
    public function testPriorityOverridesLowerProvider(): void
    {
        $registry = new InMemoryCommandRegistry(withDefaultCommands: false);

        $registry->addProvider(ProviderLow::class, 1);
        $registry->addProvider(ProviderHigh::class, 10);

        $def = $registry->get('dup:cmd');
        self::assertNotNull($def);
        self::assertSame('high', $def->description);
    }

    /**
     * Проверим, что при равном приоритете get() и all() выбирают одну и ту же команду (зарегистрированную позже).
     *
     * @see InMemoryCommandRegistry::get()
     * @see InMemoryCommandRegistry::all()
     */
    #[Test]
    public function getAndAllAgreeOnEqualPriority(): void
    {
        $byGet = new InMemoryCommandRegistry(withDefaultCommands: false);

        $byGet->addProvider(ProviderLow::class, 5);
        $byGet->addProvider(ProviderHigh::class, 5);

        $byAll = new InMemoryCommandRegistry(withDefaultCommands: false);

        $byAll->addProvider(ProviderLow::class, 5);
        $byAll->addProvider(ProviderHigh::class, 5);
        $all = $byAll->all();

        self::assertSame($all[0]->description, $byGet->get('dup:cmd')?->description);
    }
}
