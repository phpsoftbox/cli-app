<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Loader;

use PhpSoftBox\CliApp\Command\CommandDefinition;

interface CommandLoaderInterface
{
    /**
     * @param list<string> $paths
     * @return list<CommandDefinition>
     */
    public function load(array $paths): array;

    public function loadFile(string $file): ?CommandDefinition;
}
