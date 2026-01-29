<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests;

use PhpSoftBox\CliApp\Config\PackageCommandDiscovery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function bin2hex;
use function file_put_contents;
use function json_encode;
use function mkdir;
use function random_bytes;
use function rmdir;
use function sys_get_temp_dir;
use function unlink;

#[CoversClass(PackageCommandDiscovery::class)]
final class PackageCommandDiscoveryTest extends TestCase
{
    /**
     * Проверяет чтение extra.psb из installed.json.
     */
    #[Test]
    public function testDiscoverReadsExtraPsb(): void
    {
        $root        = sys_get_temp_dir() . '/cliapp_vendor_' . bin2hex(random_bytes(4));
        $vendor      = $root . '/vendor';
        $composerDir = $vendor . '/composer';
        $pkgDir      = $vendor . '/acme/foo';
        @mkdir($composerDir, 0777, true);
        @mkdir($pkgDir . '/console', 0777, true);
        @mkdir($pkgDir, 0777, true);
        file_put_contents($pkgDir . '/cli.php', '<?php return 1;');

        $installed = [
            'packages' => [
                [
                    'name'         => 'acme/foo',
                    'install-path' => '../acme/foo',
                    'extra'        => [
                        'psb' => [
                            'commandPaths' => ['console'],
                            'commandFiles' => ['cli.php'],
                            'providers'    => [
                                'Acme\\Foo\\Provider',
                                ['class' => 'Acme\\Foo\\Provider2', 'priority' => 5],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        file_put_contents($composerDir . '/installed.json', json_encode($installed));

        $found = PackageCommandDiscovery::discover($vendor);

        self::assertSame([$composerDir . '/../acme/foo/console'], $found['paths']);
        self::assertSame([$composerDir . '/../acme/foo/cli.php'], $found['files']);
        self::assertSame([
            ['class' => 'Acme\\Foo\\Provider', 'priority' => 0],
            ['class' => 'Acme\\Foo\\Provider2', 'priority' => 5],
        ], $found['providers']);

        @unlink($composerDir . '/installed.json');
        @unlink($pkgDir . '/cli.php');
        @rmdir($pkgDir . '/console');
        @rmdir($pkgDir);
        @rmdir($composerDir);
        @rmdir($vendor);
        @rmdir($root);
    }

    /**
     * Проверяет, что при отсутствии installed.json возвращаются пустые массивы.
     */
    #[Test]
    public function testDiscoverHandlesMissingInstalledJson(): void
    {
        $root = sys_get_temp_dir() . '/cliapp_vendor_' . bin2hex(random_bytes(4));
        @mkdir($root . '/vendor', 0777, true);

        $found = PackageCommandDiscovery::discover($root . '/vendor');

        self::assertSame(['paths' => [], 'files' => [], 'providers' => []], $found);

        @rmdir($root . '/vendor');
        @rmdir($root);
    }
}
