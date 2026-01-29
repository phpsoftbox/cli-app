<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Io;

interface ProgressInterface
{
    public function advance(int $step = 1): void;
    public function finish(): void;
}
