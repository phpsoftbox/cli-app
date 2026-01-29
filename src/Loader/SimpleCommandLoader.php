<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Loader;

use PhpSoftBox\CliApp\Command\CommandDefinition;

use function glob;
use function is_dir;
use function is_file;
use function rtrim;

final class SimpleCommandLoader implements CommandLoaderInterface
{
    public function load(array $paths): array
    {
        $definitions = [];
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }
            foreach (glob(rtrim($path, '/\\') . '/*.php') ?: [] as $file) {
                $def = $this->loadFile($file);
                if ($def !== null) {
                    $definitions[] = $def;
                }
            }
        }

        return $definitions;
    }

    public function loadFile(string $file): ?CommandDefinition
    {
        if (!is_file($file)) {
            return null;
        }

        $result = require $file;

        return $result instanceof CommandDefinition ? $result : null;
    }
}
