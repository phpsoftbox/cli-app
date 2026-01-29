<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests;

use PhpSoftBox\CliApp\Loader\SimpleCommandLoader;
use PhpSoftBox\CliApp\Command\Command;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SimpleCommandLoader::class)]
#[CoversClass(Command::class)]
final class SimpleCommandLoaderTest extends TestCase
{
    /**
     * Проверяет загрузку команды из PHP-файла.
     */
    #[Test]
    public function testLoadsCommandFromFile(): void
    {
        $loader = new SimpleCommandLoader();
        $file = tempnam(sys_get_temp_dir(), 'cli-cmd-') . '.php';

        file_put_contents($file, <<<'PHP'
<?php
use PhpSoftBox\CliApp\Command\Command;
use function PhpSoftBox\CliApp\flag;

return Command::define(
    'sample',
    'Sample command',
    [flag('test')],
    fn () => 0
);
PHP);

        $definition = $loader->loadFile($file);

        self::assertNotNull($definition);
        self::assertSame('sample', $definition->name);
    }
}
