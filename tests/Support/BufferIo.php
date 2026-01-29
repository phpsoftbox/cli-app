<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests\Support;

use PhpSoftBox\CliApp\Io\IoInterface;
use PhpSoftBox\CliApp\Io\ProgressInterface;

use function json_encode;

final class BufferIo implements IoInterface
{
    /** @var list<array{message:string,style:string}> */
    public array $lines = [];

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
        $this->lines[] = ['message' => $message, 'style' => $style];
    }

    public function table(array $headers, array $rows): void
    {
        $this->writeln('TABLE:' . json_encode(['headers' => $headers, 'rows' => $rows]), 'comment');
    }

    public function progress(int $max): ProgressInterface
    {
        return new class () implements ProgressInterface {
            public function advance(int $step = 1): void
            {
            }
            public function finish(): void
            {
            }
        };
    }
}
