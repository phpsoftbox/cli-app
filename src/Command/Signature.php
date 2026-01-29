<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Command;

/**
 * Сигнатура команды: аргументы и опции.
 *
 * Хранит определение структуры входных данных и умеет парсить argv.
 */
final class Signature
{
    /** @var list<ArgumentDefinition> */
    private array $arguments = [];

    /** @var array<string, OptionDefinition> keyed по длинному имени */
    private array $options = [];

    /**
     * @param list<ArgumentDefinition|OptionDefinition> $items
     */
    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            if ($item instanceof ArgumentDefinition) {
                $this->arguments[] = $item;
            } elseif ($item instanceof OptionDefinition) {
                $this->options[$item->name] = $item;
            }
        }
    }

    /**
     * Добавить позиционный аргумент.
     */
    public function addArgument(ArgumentDefinition $argument): self
    {
        $clone              = clone $this;
        $clone->arguments[] = $argument;

        return $clone;
    }

    /**
     * Добавить опцию/флаг.
     */
    public function addOption(OptionDefinition $option): self
    {
        $clone                         = clone $this;
        $clone->options[$option->name] = $option;

        return $clone;
    }

    /**
     * @return list<ArgumentDefinition>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return array<string, OptionDefinition>
     */
    public function options(): array
    {
        return $this->options;
    }
}
