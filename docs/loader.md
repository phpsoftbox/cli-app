# Загрузка и регистрация команд

Команды можно регистрировать вручную или сканировать директории.

## Реестр

- `CommandRegistryInterface` — контракт.
- `InMemoryCommandRegistry` — базовая реализация (алиасы указывают на одно определение, по умолчанию регистрирует `list`).
  Отключить можно так: `new InMemoryCommandRegistry(withDefaultCommands: false)`.
  Также добавляет глобальные опции: `--environment/-e`, `--help/-h`.

## Загрузчик файлов

- `CommandLoaderInterface` — контракт.
- `SimpleCommandLoader` — исполняет PHP-файл и ожидает `CommandDefinition`.

## Сканер

`CommandScanner` регистрирует всё найденное в реестр.
Команда `list` добавляется базовым реестром (`AbstractCommandRegistry`).

```php
$scanner = new CommandScanner(loader: new SimpleCommandLoader(), registry: $registry);
$registry = $scanner->register(
    paths: [__DIR__.'/commands'],      // все *.php в директории
    files: [__DIR__.'/vendor/pkg/Cli/Create.php'], // прямые файлы
);
```

Полученный реестр передайте в `CliApp`, а запуск выполняйте через `Runner`.

## Автоподключение пакетов (composer extra)

Пакеты могут объявлять команды через `extra.psb` в своём `composer.json`:

```json
{
  "extra": {
    "psb": {
      "commandPaths": ["console"],
      "commandFiles": ["Cli/Commands.php"],
      "providers": [
        "Vendor\\Pkg\\Cli\\Provider",
        { "class": "Vendor\\\\Pkg\\\\Cli\\\\ProviderOverride", "priority": 10 }
      ]
    }
  }
}
```

- `commandPaths` и `commandFiles` резолвятся относительно корня пакета.
- `providers` — классы, реализующие `CommandProviderInterface`, они смогут напрямую зарегистрировать команды.
  Поддерживается приоритет: больший `priority` перезапишет команды с меньшим.
