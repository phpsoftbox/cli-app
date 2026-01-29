# Bash Completion (Автокомплит)

Скрипт автокомплита дополняет только имя команды (первый аргумент) по выводу `psb list`.

## Установка

### Локально (через `.bashrc`)

Добавьте в `~/.bashrc` или `~/.bash_profile`:

```bash
source /path/to/vendor/bin/_psb_completion
```

Например, из корня проекта:

```bash
source ./vendor/bin/_psb_completion
```

Примените изменения:

```bash
source ~/.bashrc
```

### Глобально (macOS/Linux)

**macOS (если установлен bash-completion):**

```bash
cp vendor/bin/_psb_completion /usr/local/etc/bash_completion.d/psb
```

**Linux (Debian/Ubuntu):**

```bash
sudo cp vendor/bin/_psb_completion /etc/bash_completion.d/psb
```

**Linux (другие дистрибутивы):**

```bash
sudo cp vendor/bin/_psb_completion /usr/share/bash-completion/completions/psb
```

Перезапустите терминал.

## Использование

Поддерживаемые вызовы:

```bash
psb <TAB>
./vendor/bin/psb <TAB>
vendor/bin/psb <TAB>
./bin/psb <TAB>
php psb <TAB>
php ./vendor/bin/psb <TAB>
```

Автокомплит подставляет только имена команд (первый аргумент), включая многоуровневые имена вида `db:migrate:rollback`. Для аргументов и опций используется стандартный file completion Bash.

## Как это работает

1. Находит бинарь `psb` (или скрипт для `php psb`).
2. Вызывает `psb list`.
3. Берет строки, начинающиеся с `- `, и подставляет имя команды.

## Решение проблем

1. Проверьте, что `psb list` работает:
   ```bash
   vendor/bin/psb list
   ```

2. Убедитесь, что скрипт подгружен:
   ```bash
   grep "_psb_completion" ~/.bashrc
   ```

3. Перезапустите shell:
   ```bash
   exec bash
   ```

## Поддержка других shell

Пока реализован только Bash. Для Zsh/Fish нужны отдельные скрипты.
