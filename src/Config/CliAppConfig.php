<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Config;

use function is_array;
use function preg_match;
use function rtrim;

/**
 * Конфигурация для entrypoint `psb`.
 */
final class CliAppConfig
{
    /**
     * @param string|null $bootstrapFile Путь к файлу, который возвращает CliApp/Runner/callable
     * @param list<string> $commandPaths Директории с командами
     * @param list<string> $commandFiles Конкретные файлы команд
     * @param list<array{class:string,priority:int}> $commandProviders Классы-провайдеры команд
     * @param bool $withDefaultCommands Регистрировать ли дефолтные команды (list)
     */
    public function __construct(
        public readonly ?string $bootstrapFile = null,
        public readonly array $commandPaths = [],
        public readonly array $commandFiles = [],
        public readonly array $commandProviders = [],
        public readonly bool $withDefaultCommands = true,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data, string $baseDir): self
    {
        $bootstrap = isset($data['bootstrap']) ? self::resolvePath($data['bootstrap'], $baseDir) : null;
        $paths     = [];
        foreach (($data['commandPaths'] ?? $data['paths'] ?? []) as $path) {
            $paths[] = self::resolvePath((string) $path, $baseDir);
        }
        $files = [];
        foreach (($data['commandFiles'] ?? $data['files'] ?? []) as $file) {
            $files[] = self::resolvePath((string) $file, $baseDir);
        }
        $providers = [];
        foreach (($data['commandProviders'] ?? $data['providers'] ?? []) as $provider) {
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
        $withDefault = (bool) ($data['withDefaultCommands'] ?? true);

        return new self($bootstrap, $paths, $files, $providers, $withDefault);
    }

    private static function resolvePath(string $path, string $baseDir): string
    {
        if ($path === '') {
            return $path;
        }
        if ($path[0] === '/' || preg_match('~^[A-Za-z]:\\\\~', $path) === 1) {
            return $path;
        }

        return rtrim($baseDir, '/\\') . '/' . $path;
    }
}
