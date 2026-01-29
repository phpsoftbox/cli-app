<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests;

use PhpSoftBox\CliApp\Io\ConsoleIo;
use PhpSoftBox\CliApp\Io\ConsoleProgress;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function fopen;
use function fwrite;
use function rewind;
use function stream_get_contents;

#[CoversClass(ConsoleIo::class)]
#[CoversClass(ConsoleProgress::class)]
final class ConsoleIoTest extends TestCase
{
    /**
     * Проверяет, что ask() читает ввод из STDIN.
     */
    #[Test]
    public function testAskReadsFromInput(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, "answer\n");
        rewind($input);

        $output = fopen('php://memory', 'w+');

        $io = new ConsoleIo($input, $output);

        $result = $io->ask('Question?');

        self::assertSame('answer', $result);
    }

    /**
     * Проверяет, что table() выводит таблицу.
     */
    #[Test]
    public function testTableWritesOutput(): void
    {
        $output = fopen('php://memory', 'w+');
        $io     = new ConsoleIo(fopen('php://memory', 'r+'), $output);

        $io->table(['A', 'B'], [['1', '2']]);

        rewind($output);
        $text = stream_get_contents($output);
        self::assertStringContainsString('| A ', $text);
        self::assertStringContainsString('| B ', $text);
        self::assertStringContainsString('| 1 ', $text);
    }

    /**
     * Проверяет, что progress пишет процент выполнения.
     */
    #[Test]
    public function testConsoleProgressWritesPercent(): void
    {
        $output   = fopen('php://memory', 'w+');
        $progress = new ConsoleProgress($output, 10, false);

        $progress->advance(5);
        $progress->finish();

        rewind($output);
        $text = stream_get_contents($output);
        self::assertStringContainsString('%', $text);
    }
}
