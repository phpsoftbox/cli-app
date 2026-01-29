<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Command;

/**
 * Упрощённая фабрика для создания CommandDefinition.
 */
final class Command
{
    /**
     * @param non-empty-string $name
     * @param list<ArgumentDefinition|OptionDefinition> $signature
     * @param callable|string|array $handler
     * @param list<string> $aliases
     * @param array<string, mixed> $meta
     * @param list<string> $environments
     */
    public static function define(
        string $name,
        string $description,
        array $signature,
        mixed $handler,
        array $aliases = [],
        array $meta = [],
        array $environments = [],
    ): CommandDefinition {
        return new CommandDefinition(
            name: $name,
            description: $description,
            signature: new Signature($signature),
            handler: $handler,
            aliases: $aliases,
            meta: $meta,
            environments: $environments,
        );
    }
}
