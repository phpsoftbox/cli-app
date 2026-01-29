<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp;

use PhpSoftBox\CliApp\Command\ArgumentDefinition;
use PhpSoftBox\CliApp\Command\OptionDefinition;

function arg(
    string $name,
    string $description = '',
    bool $required = true,
    mixed $default = null,
    bool $variadic = false,
    string $type = 'string',
): ArgumentDefinition {
    return new ArgumentDefinition($name, $description, $required, $default, $variadic, $type);
}

function opt(
    string $name,
    ?string $short = null,
    string $description = '',
    bool $required = false,
    mixed $default = null,
    string $type = 'string',
    bool $repeatable = false,
): OptionDefinition {
    return new OptionDefinition($name, $short, $description, false, $required, $default, $type, $repeatable);
}

function flag(
    string $name,
    ?string $short = null,
    string $description = '',
): OptionDefinition {
    return new OptionDefinition($name, $short, $description, true, false, false, 'bool', false);
}
