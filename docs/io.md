# IO-хелперы

`IoInterface` абстрагирует работу со вводом/выводом:

- `ask($question, $default = null): string`
- `confirm($question, $default = false): bool`
- `secret($question): string` — базовая реализация не скрывает ввод (можно заменить свою).
- `writeln($message, $style = 'info')` — стили: info/comment/success/error (ANSI, если поддерживается).
- `table($headers, $rows)` — рисует простую таблицу.
- `progress($max): ProgressInterface` — прогресс-бар (`advance()`, `finish()`).

Реализации:
- `ConsoleIo` — читает из STDIN/STDOUT, минимальные цвета.
- `NullIo` / `NullProgress` — заглушки для тестов или тихого режима.
