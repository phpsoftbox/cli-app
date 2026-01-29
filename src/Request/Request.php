<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Request;

/**
 * Нормализованный запрос, полученный после разбора argv.
 */
final readonly class Request
{
    /**
     * @param array<string, mixed> $params Позиционные аргументы по имени
     * @param array<string, mixed> $options Опции/флаги по имени
     * @param list<string> $extra Неиспользованные токены argv
     * @param list<string> $errors Ошибки валидации/разбора
     */
    public function __construct(
        private array $params,
        private array $options,
        private array $extra = [],
        private array $errors = [],
    ) {
    }

    public function param(string $name, mixed $default = null): mixed
    {
        return $this->params[$name] ?? $default;
    }

    public function option(string $name, mixed $default = null): mixed
    {
        return $this->options[$name] ?? $default;
    }

    /** @return array<string, mixed>|mixed */
    public function params(?string $name = null, mixed $default = null): mixed
    {
        if ($name !== null) {
            return $this->params[$name] ?? $default;
        }

        return $this->params;
    }

    /** @return array<string, mixed>|mixed */
    public function options(?string $name = null, mixed $default = null): mixed
    {
        if ($name !== null) {
            return $this->options[$name] ?? $default;
        }

        return $this->options;
    }

    /** @return array<string, mixed> */
    public function args(): array
    {
        return $this->params;
    }

    /** @return list<string> */
    public function extra(): array
    {
        return $this->extra;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /** @return list<string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
