<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Command;

/**
 * Описание позиционного аргумента команды.
 */
final readonly class ArgumentDefinition
{
    /**
     * @param non-empty-string $name Имя аргумента (используется в Request::param())
     * @param string $description Текст для help
     * @param bool $required Обязателен ли аргумент
     * @param mixed $default Значение по умолчанию (если необязательный)
     * @param bool $variadic Если true — поглощает все оставшиеся значения
     * @param 'string'|'int'|'bool'|'array' $type Тип значения для приведения
     */
    public function __construct(
        public string $name,
        public string $description = '',
        public bool $required = true,
        public mixed $default = null,
        public bool $variadic = false,
        public string $type = 'string',
    ) {
    }
}
