<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Command;

use PhpSoftBox\CliApp\Loader\CommandProviderInterface;
use PhpSoftBox\CliApp\Response;
use PhpSoftBox\CliApp\Runner\RunnerInterface;

use function array_values;
use function class_exists;
use function count;
use function explode;
use function getenv;
use function ksort;
use function max;
use function mb_strlen;
use function str_repeat;
use function usort;

/**
 * Базовый реестр с дефолтными командами и ленивыми провайдерами.
 */
abstract class AbstractCommandRegistry implements CommandRegistryInterface, GlobalOptionsProviderInterface
{
    /** @var array<string, CommandDefinition> */
    protected array $commands = [];

    /** @var array<string, int> */
    protected array $commandPriorities = [];

    /** @var list<array{class:string,priority:int,loaded:bool}> */
    private array $providers = [];

    /** @var list<OptionDefinition> */
    private array $globalOptions = [];

    public function __construct(bool $withDefaultCommands = true)
    {
        $this->globalOptions = $this->defaultGlobalOptions();
        if ($withDefaultCommands) {
            $this->registerDefaultCommands();
        }
    }

    /** @return list<OptionDefinition> */
    public function globalOptions(): array
    {
        return $this->globalOptions;
    }

    /**
     * @return list<OptionDefinition>
     */
    protected function defaultGlobalOptions(): array
    {
        $env = getenv('APP_ENV') ?: 'production';

        return [
            new OptionDefinition(
                name: 'environment',
                short: 'e',
                description: 'Environment (from APP_ENV)',
                required: false,
                default: $env,
                type: 'string',
            ),
            new OptionDefinition(
                name: 'help',
                short: 'h',
                description: 'Show help',
                flag: true,
            ),
        ];
    }

    public function register(CommandDefinition $definition): void
    {
        $this->registerWithPriority($definition, 100);
    }

    public function registerWithPriority(CommandDefinition $definition, int $priority): void
    {
        $existingPriority = $this->commandPriorities[$definition->name] ?? null;
        if ($existingPriority === null || $priority >= $existingPriority) {
            $this->commands[$definition->name]          = $definition;
            $this->commandPriorities[$definition->name] = $priority;
            foreach ($definition->aliases as $alias) {
                $this->commands[$alias]          = $definition;
                $this->commandPriorities[$alias] = $priority;
            }
        }
    }

    public function get(string $name): ?CommandDefinition
    {
        if (isset($this->commands[$name])) {
            return $this->commands[$name];
        }

        $this->loadProvidersUntil(fn () => isset($this->commands[$name]));

        return $this->commands[$name] ?? null;
    }

    public function all(): array
    {
        $this->loadProvidersUntil(fn () => false, loadAll: true);

        $unique = [];
        foreach ($this->commands as $def) {
            $unique[$def->name] = $def;
        }

        return array_values($unique);
    }

    public function addProvider(string $class, int $priority = 0): void
    {
        $this->providers[] = ['class' => $class, 'priority' => $priority, 'loaded' => false];
        usort($this->providers, fn ($a, $b) => $b['priority'] <=> $a['priority']);
    }

    private function loadProvidersUntil(callable $stop, bool $loadAll = false): void
    {
        foreach ($this->providers as $i => $provider) {
            if ($provider['loaded']) {
                continue;
            }

            $class = $provider['class'];
            if (!class_exists($class)) {
                $this->providers[$i]['loaded'] = true;
                continue;
            }

            $instance = new $class();

            if (!$instance instanceof CommandProviderInterface) {
                $this->providers[$i]['loaded'] = true;
                continue;
            }

            $instance->register(new class ($this, $provider['priority']) implements CommandRegistryInterface {
                public function __construct(
                    private AbstractCommandRegistry $registry,
                    private int $priority,
                ) {
                }

                public function register(CommandDefinition $definition): void
                {
                    $this->registry->registerWithPriority($definition, $this->priority);
                }

                public function get(string $name): ?CommandDefinition
                {
                    return $this->registry->get($name);
                }

                public function all(): array
                {
                    return $this->registry->all();
                }
            });

            $this->providers[$i]['loaded'] = true;

            if (!$loadAll && $stop()) {
                return;
            }
        }
    }

    protected function registerDefaultCommands(): void
    {
        $this->registerListCommand();
    }

    protected function registerListCommand(): void
    {
        if ($this->get('list') !== null) {
            return;
        }

        $this->registerWithPriority(Command::define(
            name: 'list',
            description: 'Список доступных команд',
            signature: [],
            handler: function (RunnerInterface $runner) {
                $commands = $this->all();
                $grouped  = [];
                foreach ($commands as $cmd) {
                    $parts          = explode(':', $cmd->name, 2);
                    $ns             = count($parts) > 1 ? $parts[0] : 'general';
                    $grouped[$ns][] = [$cmd->name, $cmd->description];
                }

                ksort($grouped);

                $runner->io()->writeln('Usage:');
                $runner->io()->writeln('  command [options] [arguments]');
                $runner->io()->writeln('');
                $runner->io()->writeln('Commands:');

                $firstGroup = true;
                foreach ($grouped as $ns => $rows) {
                    if (!$firstGroup) {
                        $runner->io()->writeln('');
                    }
                    $firstGroup = false;

                    $runner->io()->writeln($ns, 'comment');

                    $maxName = 0;
                    foreach ($rows as $row) {
                        $maxName = max($maxName, mb_strlen((string) $row[0]));
                    }

                    foreach ($rows as $row) {
                        $name = (string) $row[0];
                        $desc = (string) ($row[1] ?? '');
                        $pad  = str_repeat(' ', $maxName - mb_strlen($name));
                        $runner->io()->writeln('- ' . $name . $pad . '  ' . $desc);
                    }
                }

                return Response::SUCCESS;
            },
        ), 0);
    }
}
