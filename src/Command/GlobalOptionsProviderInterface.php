<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Command;

/**
 * Контракт для реестров, которые предоставляют глобальные опции.
 */
interface GlobalOptionsProviderInterface
{
    /** @return list<OptionDefinition> */
    public function globalOptions(): array;
}
