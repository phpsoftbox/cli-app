<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Loader;

use PhpSoftBox\CliApp\Command\CommandRegistryInterface;

final readonly class CommandScanner
{
    public function __construct(
        private CommandLoaderInterface $loader,
        private CommandRegistryInterface $registry,
    ) {
    }

    /** @param list<string> $paths @param list<string> $files */
    public function register(array $paths = [], array $files = []): CommandRegistryInterface
    {
        foreach ($this->loader->load($paths) as $definition) {
            $this->registry->register($definition);
        }

        foreach ($files as $file) {
            $def = $this->loader->loadFile($file);
            if ($def !== null) {
                $this->registry->register($def);
            }
        }

        return $this->registry;
    }
}
