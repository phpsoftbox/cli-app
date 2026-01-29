<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Io;

interface IoInterface
{
    public function ask(string $question, ?string $default = null): string;
    public function confirm(string $question, bool $default = false): bool;
    public function secret(string $question): string;
    public function writeln(string $message, string $style = 'info'): void;
    /** @param list<string> $headers @param list<list<string|int|float|null>> $rows */
    public function table(array $headers, array $rows): void;
    public function progress(int $max): ProgressInterface;
}
