<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Command;

/**
 * DTO с описанием консольной команды.
 *
 * Содержит имя, описание, сигнатуру и обработчик.
 * Предполагается, что объекты создаются через фабрику Command::define().
 */
final class CommandDefinition
{
    /**
     * @param non-empty-string $name Уникальное имя команды (например migrate:up)
     * @param string $description Краткое описание для help
     * @param Signature $signature Описание аргументов/опций
     * @param callable|string|array $handler Обработчик, вызываемый Runner
     * @param list<string> $aliases Дополнительные имена
     * @param array<string, mixed> $meta Произвольные метаданные (group/tags/sort и т.д.)
     * @param list<string> $environments Разрешённые окружения (пусто = любые)
     */
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly Signature $signature,
        /** @var callable */
        public readonly mixed $handler,
        public readonly array $aliases = [],
        public readonly array $meta = [],
        public readonly array $environments = [],
    ) {
    }
}
