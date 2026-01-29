# Разбор argv и Request

Парсер (`RequestParser`) получает `Signature` и массив `argv` (без имени команды) и возвращает `Request`.

Поддерживается:
- `--opt=value` и `--opt value`
- `-o value`, `-oValue`, пакет коротких флагов `-abc`
- флаги (без значения)
- позиционные аргументы по порядку, `variadic` поглощает остаток

Ошибки (неизвестная опция, отсутствующее значение, обязательный аргумент) накапливаются в `Request::errors()` и делают `hasErrors() === true`.

## API Request

- `param($name, $default = null)` — позиционный аргумент.
- `option($name, $default = null)` — опция/флаг.
- `params(?$name = null)` / `options(?$name = null)` — сразу все или по имени.
- `args()` — синоним `params()`.
- `extra()` — токены, не распознанные сигнатурой.
- `errors()` / `hasErrors()` — сообщения о проблемах разбора/валидации.
