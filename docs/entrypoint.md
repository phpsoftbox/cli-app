# Entrypoint `psb`

При установке пакета в `vendor/bin` появится исполняемый скрипт `psb`.

Поведение:

- Ищет автолоадер (`vendor/autoload.php`).
- Пытается загрузить конфиг по `PSB_CLI_APP_CONFIG_PATH` (если задана), иначе `config/cli-app.php`:
  - файл может вернуть `CliApp`, `RunnerInterface`, `CliAppConfig` или массив настроек;
  - допускается возврат callable, который вернёт один из этих вариантов.
- Если конфиг не найден, по умолчанию сканируются директории `./console` и `./commands`
  и создаётся `CliApp` на базе `InMemoryCommandRegistry` и `ConsoleIo`.

Запуск из проекта:

```bash
php ./vendor/bin/psb list
```

Если хочется короче, можно сделать симлинк в корне проекта:

```bash
ln -s ./vendor/bin/psb ./psb
php ./psb list
```

## Пример `config/cli-app.php` (массив)

```php
<?php
return [
    // файл, который возвращает CliApp/Runner/callable
    'bootstrap' => __DIR__ . '/../app/cli.php',
    // директории с командами (если bootstrap не задан)
    'commandPaths' => [__DIR__ . '/../console', __DIR__ . '/../commands'],
    // конкретные файлы команд
    'commandFiles' => [__DIR__ . '/../vendor/pkg/cli.php'],
    // классы-провайдеры команд (CommandProviderInterface)
    'commandProviders' => ['Vendor\\Pkg\\Cli\\Provider'],
    // регистрировать ли дефолтные команды (list)
    'withDefaultCommands' => true,
];
```

## Пример `config/cli-app.php` (CliAppConfig)

```php
<?php
use PhpSoftBox\CliApp\Config\CliAppConfig;

return new CliAppConfig(
    bootstrapFile: __DIR__ . '/../app/cli.php',
    commandPaths: [__DIR__ . '/../console'],
    commandFiles: [],
    withDefaultCommands: true,
);
```
