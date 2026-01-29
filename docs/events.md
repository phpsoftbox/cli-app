# События

Runner публикует события через `EventDispatcherInterface`:

- `beforeRun` — перед выполнением обработчика (`command`, `argv`).
- `afterRun` — после завершения (`command`, `response`).
- `error` — при ошибке (нет команды, ошибки валидации) (`command`?, `response`).
- `question` — при вызове `Io::ask()` / `confirm()` (`question`).
- `output` — при `Io::writeln()` (`message`, `style`).

Реализации:
- `EventDispatcher` — простой список подписчиков.
- `NullEventDispatcher` — ничего не делает.

Подписка:

```php
$events->subscribe(Events::AFTER_RUN, function (array $payload) {
    // логируем или метрики
});
```
