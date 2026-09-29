<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Io;

use PhpSoftBox\CliApp\Events\EventDispatcherInterface;
use PhpSoftBox\CliApp\Events\Events;

use function array_map;
use function array_merge;
use function escapeshellarg;
use function fgets;
use function function_exists;
use function fwrite;
use function implode;
use function in_array;
use function is_resource;
use function is_string;
use function max;
use function mb_strlen;
use function posix_isatty;
use function rtrim;
use function shell_exec;
use function str_repeat;
use function stream_isatty;
use function strtolower;
use function trim;

use const PHP_EOL;
use const STDIN;
use const STDOUT;

final class ConsoleIo implements IoInterface
{
    private $stdin;
    private $stdout;
    private bool $supportsAnsi;

    public function __construct(
        $stdin = null,
        $stdout = null,
        private readonly ?EventDispatcherInterface $events = null,
    ) {
        $this->stdin        = $stdin ?? STDIN;
        $this->stdout       = $stdout ?? STDOUT;
        $this->supportsAnsi = $this->detectAnsi();
    }

    public function ask(string $question, ?string $default = null): string
    {
        $prompt = $default === null ? "$question: " : "$question [$default]: ";
        $this->writeRaw($prompt);
        $this->events?->dispatch(Events::QUESTION, ['question' => $question]);
        $line   = fgets($this->stdin);
        $answer = $line === false ? '' : rtrim($line, "\r\n");

        return $answer !== '' ? $answer : ($default ?? '');
    }

    public function confirm(string $question, bool $default = false): bool
    {
        $suffix = $default ? '[Y/n]' : '[y/N]';
        $answer = strtolower($this->ask("{$question} {$suffix}", $default ? 'y' : 'n'));

        return in_array($answer, ['y', 'yes', '1', 'true'], true);
    }

    /**
     * Ввод без отображения: в терминале эхо выключается через `stty -echo`. Без терминала (pipe, тесты) ввод читается
     * как обычно — показывать там нечего.
     */
    public function secret(string $question): string
    {
        if (!$this->isInteractiveTerminal()) {
            return $this->ask($question);
        }

        $mode = shell_exec('stty -g 2>/dev/null');
        if (!is_string($mode) || trim($mode) === '') {
            return $this->ask($question);
        }

        shell_exec('stty -echo 2>/dev/null');

        try {
            $answer = $this->ask($question);
        } finally {
            shell_exec('stty ' . escapeshellarg(trim($mode)) . ' 2>/dev/null');
            $this->writeRaw(PHP_EOL);
        }

        return $answer;
    }

    private function isInteractiveTerminal(): bool
    {
        if (!function_exists('shell_exec') || !is_resource($this->stdin)) {
            return false;
        }

        if (function_exists('stream_isatty')) {
            return stream_isatty($this->stdin);
        }

        return function_exists('posix_isatty') && posix_isatty($this->stdin);
    }

    public function writeln(string $message, string $style = 'info'): void
    {
        $styled = $this->applyStyle($message, $style);
        $this->writeRaw($styled . PHP_EOL);
        $this->events?->dispatch(Events::OUTPUT, ['message' => $message, 'style' => $style]);
    }

    public function table(array $headers, array $rows): void
    {
        $allRows = array_merge([$headers], $rows);
        $widths  = [];
        foreach ($allRows as $row) {
            foreach ($row as $idx => $col) {
                $len          = mb_strlen((string) $col);
                $widths[$idx] = max($widths[$idx] ?? 0, $len);
            }
        }

        $divider = '+' . implode('+', array_map(fn ($w) => str_repeat('-', $w + 2), $widths)) . '+';

        $this->writeln($divider);
        $this->writeln($this->formatRow($headers, $widths));
        $this->writeln($divider);
        foreach ($rows as $row) {
            $this->writeln($this->formatRow($row, $widths));
        }
        $this->writeln($divider);
    }

    public function progress(int $max): ProgressInterface
    {
        return new ConsoleProgress($this->stdout, $max, $this->supportsAnsi);
    }

    private function formatRow(array $row, array $widths): string
    {
        $cells = [];
        foreach ($widths as $idx => $width) {
            $value   = (string) ($row[$idx] ?? '');
            $cells[] = ' ' . $this->padRight($value, $width) . ' ';
        }

        return '|' . implode('|', $cells) . '|';
    }

    private function writeRaw(string $text): void
    {
        fwrite($this->stdout, $text);
    }

    private function detectAnsi(): bool
    {
        if (function_exists('stream_isatty')) {
            return @stream_isatty(STDOUT);
        }
        if (function_exists('posix_isatty')) {
            return @posix_isatty(STDOUT);
        }

        return false;
    }

    private function applyStyle(string $text, string $style): string
    {
        if (!$this->supportsAnsi) {
            return $text;
        }

        $map = [
            'info'    => '0;37',
            'comment' => '0;33',
            'success' => '0;32',
            'error'   => '0;31',
        ];

        $code = $map[$style] ?? null;
        if ($code === null) {
            return $text;
        }

        return "\033[{$code}m{$text}\033[0m";
    }

    private function padRight(string $value, int $width): string
    {
        $len = mb_strlen($value);
        if ($len >= $width) {
            return $value;
        }

        return $value . str_repeat(' ', $width - $len);
    }
}
