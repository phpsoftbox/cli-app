<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Command;

use function array_map;
use function implode;
use function is_array;
use function is_bool;
use function is_string;
use function max;
use function str_pad;
use function strlen;

use const PHP_EOL;

/**
 * Формирует строку Usage и подсказку по аргументам/опциям.
 */
final class UsageFormatter
{
    public function formatUsage(CommandDefinition $definition): string
    {
        $parts = ['Usage:', $definition->name];

        foreach ($definition->signature->options() as $opt) {
            $optPart = '--' . $opt->name;
            if ($opt->short) {
                $optPart .= "|-{$opt->short}";
            }
            if (!$opt->flag) {
                $optPart .= ' <value>';
            }
            $parts[] = $opt->required ? $optPart : '[' . $optPart . ']';
        }

        foreach ($definition->signature->arguments() as $arg) {
            $name = '<' . $arg->name . '>';
            if (!$arg->required) {
                $name = '[' . $name . ']';
            }
            if ($arg->variadic) {
                $name .= '...';
            }
            $parts[] = $name;
        }

        return implode(' ', $parts);
    }

    public function formatHelp(CommandDefinition $definition): string
    {
        $lines = [];

        if ($definition->description !== '') {
            $lines[] = $definition->name . ' — ' . $definition->description;
            $lines[] = '';
        }

        if ($definition->aliases !== []) {
            $lines[] = 'Aliases: ' . implode(', ', $definition->aliases);
            $lines[] = '';
        }

        $lines[] = $this->formatUsage($definition);

        $args = $definition->signature->arguments();
        if ($args !== []) {
            $lines[] = '';
            $lines[] = 'Arguments:';
            $max     = 0;
            foreach ($args as $arg) {
                $label = '<' . $arg->name . '>';
                if (!$arg->required) {
                    $label = '[' . $label . ']';
                }
                if ($arg->variadic) {
                    $label .= '...';
                }
                $max = max($max, strlen($label));
            }
            foreach ($args as $arg) {
                $label = '<' . $arg->name . '>';
                if (!$arg->required) {
                    $label = '[' . $label . ']';
                }
                if ($arg->variadic) {
                    $label .= '...';
                }
                $desc = $arg->description;
                $desc .= ($desc !== '' ? ' ' : '') . '(type: ' . $arg->type . ')';
                if ($arg->default !== null) {
                    $desc .= ($desc !== '' ? ' ' : '') . '(default: ' . $this->stringify($arg->default) . ')';
                }
                $lines[] = '  ' . str_pad($label, $max + 2) . $desc;
            }
        }

        $opts = $definition->signature->options();
        if ($opts !== []) {
            $lines[] = '';
            $lines[] = 'Options:';
            $max     = 0;
            foreach ($opts as $opt) {
                $label = $opt->short ? '-' . $opt->short . ', --' . $opt->name : '--' . $opt->name;
                if (!$opt->flag) {
                    $label .= ' <value>';
                }
                $max = max($max, strlen($label));
            }
            foreach ($opts as $opt) {
                $label = $opt->short ? '-' . $opt->short . ', --' . $opt->name : '--' . $opt->name;
                if (!$opt->flag) {
                    $label .= ' <value>';
                }
                $desc = $opt->description;
                if ($opt->required) {
                    $desc .= ($desc !== '' ? ' ' : '') . '(required)';
                }
                $desc .= ($desc !== '' ? ' ' : '') . '(type: ' . $opt->type . ')';
                if ($opt->default !== null && !$opt->flag) {
                    $desc .= ($desc !== '' ? ' ' : '') . '(default: ' . $this->stringify($opt->default) . ')';
                }
                if ($opt->repeatable) {
                    $desc .= ($desc !== '' ? ' ' : '') . '(repeatable)';
                }
                $lines[] = '  ' . str_pad($label, $max + 2) . $desc;
            }
        }

        return implode(PHP_EOL, $lines);
    }

    private function stringify(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_array($value)) {
            return '[' . implode(', ', array_map('strval', $value)) . ']';
        }

        return (string) $value;
    }
}
