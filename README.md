# PhpSoftBox CliApp
 
Команды описываются обычными PHP-файлами, которые возвращают `CommandDefinition` (через фабрику `Command::define()`), 
а сам раннер умеет разбирать `argv`, спрашивать пользователя (ask/confirm/secret), рисовать таблицы и прогресс, рассылать
события и работать как с DI-контейнером (PSR-11), так и без него.

По умолчанию реестр команд (`AbstractCommandRegistry`) добавляет команду `list`,
которая выводит все зарегистрированные команды по namespace (часть до `:`).
Отключить можно так: `new InMemoryCommandRegistry(withDefaultCommands: false)`.

## Entrypoint `psb`

После установки пакета доступен `vendor/bin/psb`. Он ищет конфиг по `PSB_CLI_APP_CONFIG_PATH` (если задана),
иначе `config/cli-app.php` и ожидает получить `CliApp`, `RunnerInterface` или `CliAppConfig`.
Если файла нет — сканирует `./console` и `./commands`.

## Установка

```bash
composer require phpsoftbox/cli-app
```

## Быстрый старт

```php
<?php
use PhpSoftBox\CliApp\CliApp;
use PhpSoftBox\CliApp\Command\Command;
use PhpSoftBox\CliApp\Command\{ArgumentDefinition as Arg};
use PhpSoftBox\CliApp\Io\ConsoleIo;
use PhpSoftBox\CliApp\Command\InMemoryCommandRegistry;
use PhpSoftBox\CliApp\Response;
use PhpSoftBox\CliApp\Runner\Runner;

$registry = new InMemoryCommandRegistry();

$registry->register(Command::define(
    name: 'hello',
    description: 'Приветствие',
    signature: [new Arg('name', 'Кого приветствовать')],
    handler: function (Runner $runner) {
        $name = $runner->request()->param('name');
        $runner->io()->writeln("Hello, $name", 'success');
        return Response::SUCCESS;
    },
));

$app = new CliApp($registry, new ConsoleIo());
$app->runner()->run('hello', ['world']);
```

## Автокомплит для Bash

Чтобы включить автокомплит для команды `psb` в Bash, добавьте в ваш `.bashrc` или `.bash_profile`:

```bash
source path/to/vendor/bin/_psb_completion
```

или скопируйте файл в стандартную директорию с завершениями (если она есть):

```bash
cp vendor/bin/_psb_completion /usr/local/etc/bash_completion.d/psb
```

После этого при вводе `psb ` + `TAB` будут предложены доступные команды.

## Документация

- [docs/index.md](docs/index.md) — оглавление
- Основные темы: структура команд, сигнатуры и парсер, запрос/ответ, IO-хелперы, события, загрузка команд из директорий.
