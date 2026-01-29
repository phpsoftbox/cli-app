# Runner

`Runner` отвечает за выполнение команд: парсит argv, формирует `Request`, вызывает обработчик и возвращает `Response`.

## Основные методы

- `run($command, $argv)` — выполнить команду (если имя пустое — пытается запустить `list`).
- `runSubCommand($command, $argv)` — изящный запуск подкоманды (использует clone Runner).
- `request()` — текущий `Request`.
- `io()` — доступ к IO-хелперам.
- `ErrorHandler` обрабатывает ошибки входных данных и выводит Usage.

## Пример subcommand

```php
return Command::define(
    'db:refresh',
    'Drop + migrate + seed',
    [],
    function (RunnerInterface $runner) {
        $runner->runSubCommand('db:drop', []);
        $runner->runSubCommand('migrate:up', ['--force']);
        $runner->runSubCommand('db:seed', []);
        return \PhpSoftBox\CliApp\Response::SUCCESS;
    }
);
```
