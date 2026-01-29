<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Request;

use PhpSoftBox\CliApp\Command\Signature;

use function array_key_exists;
use function array_map;
use function array_merge;
use function array_slice;
use function count;
use function explode;
use function filter_var;
use function implode;
use function in_array;
use function str_contains;
use function str_split;
use function str_starts_with;
use function strlen;
use function strtolower;
use function substr;

use const FILTER_VALIDATE_INT;

/**
 * Парсер argv согласно сигнатуре.
 */
final class RequestParser
{
    /**
     * @param list<string> $argv
     */
    public static function parse(Signature $signature, array $argv): Request
    {
        $params  = [];
        $options = [];
        $errors  = [];
        $extra   = [];

        $shortMap = [];
        foreach ($signature->options() as $opt) {
            if ($opt->short !== null) {
                $shortMap[$opt->short] = $opt->name;
            }
            if ($opt->default !== null) {
                $options[$opt->name] = $opt->default;
            } elseif ($opt->flag) {
                $options[$opt->name] = false;
            }
        }

        $argDefs  = $signature->arguments();
        $argIndex = 0;
        $argc     = count($argv);

        for ($i = 0; $i < $argc; $i++) {
            $token = $argv[$i];

            if (str_starts_with($token, '--')) {
                $nameValue                 = substr($token, 2);
                [$name, $value, $hasValue] = self::splitLong($nameValue);

                $opt = $signature->options()[$name] ?? null;
                if ($opt === null) {
                    $errors[] = "Неизвестная опция --{$name}";
                    continue;
                }

                if ($opt->flag) {
                    if ($hasValue) {
                        $errors[] = "Флаг --{$name} не принимает значение";
                        continue;
                    }
                    $options[$opt->name] = true;
                    continue;
                }

                if (!$hasValue) {
                    $value = $argv[$i + 1] ?? null;
                    if ($value === null) {
                        $errors[] = "Опции --{$name} требуется значение";
                        continue;
                    }
                    $i++;
                }

                $casted = self::cast($value, $opt->type, $errors, "--{$name}");
                if ($opt->repeatable) {
                    $options[$opt->name] ??= [];
                    $options[$opt->name][] = $casted;
                } else {
                    $options[$opt->name] = $casted;
                }
                continue;
            }

            if (str_starts_with($token, '-') && $token !== '-') {
                $shorts        = substr($token, 1);
                $consumedFully = false;

                if (strlen($shorts) >= 2 && isset($shortMap[$shorts[0]]) && !isset($shortMap[$shorts[1]])) {
                    $name  = $shortMap[$shorts[0]];
                    $opt   = $signature->options()[$name];
                    $value = substr($shorts, 1);
                    if ($opt->flag) {
                        $errors[] = "Флаг -{$shorts[0]} не принимает встроенное значение";
                        continue;
                    }
                    $casted         = self::cast($value, $opt->type, $errors, "-{$shorts[0]}");
                    $options[$name] = $opt->repeatable ? array_merge($options[$name] ?? [], [$casted]) : $casted;
                    continue;
                }

                $letters = str_split($shorts);
                foreach ($letters as $index => $letter) {
                    $name = $shortMap[$letter] ?? null;
                    if ($name === null) {
                        $errors[] = "Неизвестная опция -{$letter}";
                        continue;
                    }
                    $opt = $signature->options()[$name];
                    if ($opt->flag) {
                        $options[$name] = true;
                        continue;
                    }
                    $inline = implode('', array_slice($letters, $index + 1));
                    if ($inline !== '') {
                        $value         = $inline;
                        $consumedFully = true;
                    } else {
                        $value = $argv[$i + 1] ?? null;
                        if ($value === null) {
                            $errors[] = "Опции -{$letter} требуется значение";
                            continue;
                        }
                        $i++;
                    }
                    $casted         = self::cast($value, $opt->type, $errors, "-{$letter}");
                    $options[$name] = $opt->repeatable ? array_merge($options[$name] ?? [], [$casted]) : $casted;
                    if ($consumedFully) {
                        break;
                    }
                }
                continue;
            }

            $argDef = $argDefs[$argIndex] ?? null;
            if ($argDef === null) {
                $extra[] = $token;
                continue;
            }

            if ($argDef->variadic) {
                $slice  = array_slice($argv, $i);
                $casted = array_map(
                    fn (string $value) => self::cast($value, $argDef->type, $errors, $argDef->name),
                    $slice,
                );
                $params[$argDef->name] = $casted;
                break;
            }

            $params[$argDef->name] = self::cast($token, $argDef->type, $errors, $argDef->name);
            $argIndex++;
        }

        foreach ($argDefs as $argDef) {
            if (!array_key_exists($argDef->name, $params)) {
                if ($argDef->required && !$argDef->variadic) {
                    $errors[] = "Обязательный аргумент <{$argDef->name}> не задан";
                } elseif ($argDef->default !== null) {
                    $params[$argDef->name] = $argDef->default;
                }
            }
        }

        foreach ($signature->options() as $opt) {
            if ($opt->required && !$opt->flag && !array_key_exists($opt->name, $options)) {
                $errors[] = "Обязательная опция --{$opt->name} не задана";
            }
        }

        return new Request($params, $options, $extra, $errors);
    }

    /** @return array{0:string,1:string|null,2:bool} */
    private static function splitLong(string $payload): array
    {
        if (str_contains($payload, '=')) {
            [$name, $value] = explode('=', $payload, 2);

            return [$name, $value, true];
        }

        return [$payload, null, false];
    }

    private static function cast(string $value, string $type, array &$errors, string $source): mixed
    {
        return match ($type) {
            'int' => filter_var($value, FILTER_VALIDATE_INT) !== false
                ? (int) $value
                : self::fail("Невозможно привести '{$value}' к int для {$source}", $errors),
            'bool'  => in_array(strtolower($value), ['1', 'true', 'yes', 'y', 'on'], true),
            'array' => [$value],
            default => $value,
        };
    }

    private static function fail(string $message, array &$errors): null
    {
        $errors[] = $message;

        return null;
    }
}
