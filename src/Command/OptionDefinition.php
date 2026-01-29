<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Command;

/**
 * Описание именованной опции (длинная/короткая форма).
 */
final class OptionDefinition
{
    /**
     * @param non-empty-string $name Имя опции в длинной форме (--name)
     * @param string|null $short Однобуквенное короткое имя (-n) или null
     * @param string $description Текст для help
     * @param bool $flag Если true — это флаг без значения
     * @param bool $required Требуется ли значение (для не-флагов)
     * @param mixed $default Значение по умолчанию
     * @param 'string'|'int'|'bool'|'array' $type Тип значения для приведения
     * @param bool $repeatable Разрешено ли указывать опцию несколько раз
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $short = null,
        public readonly string $description = '',
        public readonly bool $flag = false,
        public readonly bool $required = false,
        public readonly mixed $default = null,
        public readonly string $type = 'string',
        public readonly bool $repeatable = false,
    ) {
    }
}
