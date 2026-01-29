<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Io;

final class NullIo implements IoInterface
{
    public function ask(string $question, ?string $default = null): string
    {
        return $default ?? '';
    }

    public function confirm(string $question, bool $default = false): bool
    {
        return $default;
    }

    public function secret(string $question): string
    {
        return '';
    }

    public function writeln(string $message, string $style = 'info'): void
    {
    }

    public function table(array $headers, array $rows): void
    {
    }

    public function progress(int $max): ProgressInterface
    {
        return new NullProgress();
    }
}
