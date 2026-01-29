<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests;

use PhpSoftBox\CliApp\Command\Command;
use PhpSoftBox\CliApp\Command\InMemoryCommandRegistry;
use PhpSoftBox\CliApp\Loader\CommandScanner;
use PhpSoftBox\CliApp\Loader\SimpleCommandLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function bin2hex;
use function file_put_contents;
use function mkdir;
use function random_bytes;
use function rmdir;
use function sys_get_temp_dir;
use function unlink;

#[CoversClass(CommandScanner::class)]
#[CoversClass(SimpleCommandLoader::class)]
#[CoversClass(InMemoryCommandRegistry::class)]
#[CoversClass(Command::class)]
final class CommandScannerTest extends TestCase
{
    /**
     * Проверяет регистрацию команд из директорий и отдельных файлов.
     */
    #[Test]
    public function testRegistersCommandsFromPathsAndFiles(): void
    {
        $dir = sys_get_temp_dir() . '/cliapp_cmds_' . bin2hex(random_bytes(4));
        mkdir($dir);

        $file1 = $dir . '/a.php';
        file_put_contents($file1, <<<'PHP'
<?php
use PhpSoftBox\CliApp\Command\Command;

return Command::define(
    'one',
    'One command',
    [],
    fn () => 0
);
PHP);

        $file2 = $dir . '/b.php';
        file_put_contents($file2, <<<'PHP'
<?php
use PhpSoftBox\CliApp\Command\Command;

return Command::define(
    'two',
    'Two command',
    [],
    fn () => 0
);
PHP);

        $registry = new InMemoryCommandRegistry(withDefaultCommands: false);

        $scanner = new CommandScanner(new SimpleCommandLoader(), $registry);

        $returned = $scanner->register(paths: [$dir], files: [$file2]);

        self::assertSame($registry, $returned);
        self::assertNotNull($registry->get('one'));
        self::assertNotNull($registry->get('two'));

        @unlink($file1);
        @unlink($file2);
        @rmdir($dir);
    }
}
