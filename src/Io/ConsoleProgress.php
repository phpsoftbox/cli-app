<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Io;

use function floor;
use function fwrite;
use function min;
use function sprintf;
use function str_repeat;

use const PHP_EOL;

final class ConsoleProgress implements ProgressInterface
{
    private int $current = 0;

    public function __construct(
        private $stdout,
        private readonly int $max,
        private readonly bool $supportsAnsi = false,
    ) {
        $this->render();
    }

    public function advance(int $step = 1): void
    {
        $this->current = min($this->current + $step, $this->max);
        $this->render();
    }

    public function finish(): void
    {
        $this->current = $this->max;
        $this->render(true);
    }

    private function render(bool $final = false): void
    {
        $percent  = $this->max > 0 ? (int) floor($this->current * 100 / $this->max) : 100;
        $barWidth = 30;
        $filled   = (int) floor($barWidth * $percent / 100);
        $bar      = str_repeat('#', $filled) . str_repeat('-', $barWidth - $filled);
        $line     = sprintf('[%s] %3d%%', $bar, $percent);

        if ($this->supportsAnsi) {
            fwrite($this->stdout, "\r" . $line);
            if ($final) {
                fwrite($this->stdout, PHP_EOL);
            }
        } else {
            fwrite($this->stdout, $line . PHP_EOL);
        }
    }
}
