<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Io;

final class NullProgress implements ProgressInterface
{
    public function advance(int $step = 1): void
    {
    }
    public function finish(): void
    {
    }
}
