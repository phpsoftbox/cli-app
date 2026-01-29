<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Config;

use function dirname;
use function file_get_contents;
use function is_array;
use function is_file;
use function json_decode;
use function preg_match;
use function rtrim;

/**
 * Читает composer installed.json и собирает команды из extra.psb.
 */
final class PackageCommandDiscovery
{
    /**
     * @return array{paths:list<string>,files:list<string>,providers:list<array{class:string,priority:int}>}
     */
    public static function discover(string $vendorDir): array
    {
        $installed = rtrim($vendorDir, '/\\') . '/composer/installed.json';
        if (!is_file($installed)) {
            return ['paths' => [], 'files' => [], 'providers' => []];
        }

        $json = file_get_contents($installed);
        if ($json === false) {
            return ['paths' => [], 'files' => [], 'providers' => []];
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return ['paths' => [], 'files' => [], 'providers' => []];
        }

        $packages = $data['packages'] ?? $data;
        if (!is_array($packages)) {
            return ['paths' => [], 'files' => [], 'providers' => []];
        }

        $paths     = [];
        $files     = [];
        $providers = [];

        foreach ($packages as $pkg) {
            if (!is_array($pkg)) {
                continue;
            }
            $extra = $pkg['extra']['psb'] ?? null;
            if (!is_array($extra)) {
                continue;
            }

            $installPath = $pkg['install-path'] ?? null;
            $base        = self::resolveInstallPath($installPath, $installed, $vendorDir, $pkg['name'] ?? null);

            foreach (($extra['commandPaths'] ?? $extra['paths'] ?? []) as $path) {
                $paths[] = self::resolvePath((string) $path, $base);
            }
            foreach (($extra['commandFiles'] ?? $extra['files'] ?? []) as $file) {
                $files[] = self::resolvePath((string) $file, $base);
            }
            foreach (($extra['providers'] ?? []) as $provider) {
                if (is_array($provider)) {
                    $class = (string) ($provider['class'] ?? '');
                    if ($class !== '') {
                        $providers[] = ['class' => $class, 'priority' => (int) ($provider['priority'] ?? 0)];
                    }
                    continue;
                }
                $class = (string) $provider;
                if ($class !== '') {
                    $providers[] = ['class' => $class, 'priority' => 0];
                }
            }
        }

        return ['paths' => $paths, 'files' => $files, 'providers' => $providers];
    }

    private static function resolveInstallPath(?string $installPath, string $installedFile, string $vendorDir, ?string $name): string
    {
        if ($installPath === null || $installPath === '') {
            if ($name === null) {
                return rtrim($vendorDir, '/\\');
            }

            return rtrim($vendorDir, '/\\') . '/' . $name;
        }

        // absolute
        if ($installPath[0] === '/' || preg_match('~^[A-Za-z]:\\\\~', $installPath) === 1) {
            return $installPath;
        }

        // relative to composer dir
        $base = dirname($installedFile);

        return rtrim($base, '/\\') . '/' . $installPath;
    }

    private static function resolvePath(string $path, string $base): string
    {
        if ($path === '') {
            return $path;
        }
        if ($path[0] === '/' || preg_match('~^[A-Za-z]:\\\\~', $path) === 1) {
            return $path;
        }

        return rtrim($base, '/\\') . '/' . $path;
    }
}
