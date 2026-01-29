# Команды и сигнатуры

## Формат файла команды

Файл должен возвращать `CommandDefinition`. Удобнее использовать фабрику `Command::define()` и короткие хелперы `arg()/opt()/flag()` (см. `src/functions.php`).
Обработчик (closure/`__invoke`/класс) получает `RunnerInterface`.

```php
<?php
use PhpSoftBox\CliApp\Runner\RunnerInterface;
use PhpSoftBox\CliApp\Command\Command;
use PhpSoftBox\CliApp\Response;
use function PhpSoftBox\CliApp\{arg, opt, flag};

return Command::define(
    name: 'migrate:up',
    description: 'Запустить миграции',
    signature: [
        arg('path', 'Путь к миграциям', required: false, default: 'database/migrations'),
        opt('step', 's', 'Сколько миграций применить', required: false, type: 'int'),
        flag('force', 'f', 'Пропустить подтверждение'),
    ],
    handler: function (RunnerInterface $runner) {
        $req = $runner->request();
        if (!$req->option('force') && !$runner->io()->confirm('Продолжить?')) {
            return Response::FAILURE;
        }
        // ... выполнить действие ...
        return Response::SUCCESS;
    },
    aliases: ['migrate'],
    meta: ['group' => 'db'],
);
```

## Signature

- `ArgumentDefinition`: имя, описание, `required`, `default`, `variadic`, `type (string|int|bool|array)`.
- `OptionDefinition`: длинное и короткое имя, `flag` (без значения), `required` (для опций со значением), `default`, `repeatable`, `type`.
- `Signature` хранит список аргументов и опций и используется парсером.

`UsageFormatter` выводит подробный help: описание, алиасы, usage, список аргументов/опций с типами и значениями по умолчанию.

### Типы

- `string` — без приведения
- `int` — через `FILTER_VALIDATE_INT`
- `bool` — true для `1/true/yes/y/on`, иначе false
- `array` — каждый встреченный элемент складывается в массив (для repeatable опций удобно)

### Алиасы

`aliases` в `Command::define()` позволяют вызывать команду под несколькими именами. В реестре все алиасы указывают на один `CommandDefinition`.

## HandlerInterface

Если вместо замыкания использовать класс-обработчик, он может реализовать `HandlerInterface`:

```php
use PhpSoftBox\CliApp\Command\HandlerInterface;
use PhpSoftBox\CliApp\Runner\RunnerInterface;
use PhpSoftBox\CliApp\Response;

final class MigrateUpHandler implements HandlerInterface
{
    public function __construct(/* зависимости из контейнера */) {}

    public function run(RunnerInterface $runner): int|Response
    {
        // ...
        return Response::SUCCESS;
    }
}
```

## Meta

`meta` — произвольные метаданные, которые не участвуют в выполнении, но полезны для UI/списков/группировки. Примеры:
- `meta: ['group' => 'db']` — для группировки в `list`
- `meta: ['tags' => ['migration','safe']]`
- `meta: ['sort' => 10]`

## environments

Параметр `environments` в `Command::define()` ограничивает запуск команды по окружению:

```php
return Command::define(
    name: 'db:seed',
    description: 'Seed database',
    signature: [],
    handler: fn (RunnerInterface $runner) => Response::SUCCESS,
    environments: ['local', 'staging'],
);
```

Глобальная опция `--environment` (`-e`) добавляется автоматически через реестр.
