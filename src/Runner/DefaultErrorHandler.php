<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Runner;

use PhpSoftBox\CliApp\Command\CommandDefinition;
use PhpSoftBox\CliApp\Command\UsageFormatter;
use PhpSoftBox\CliApp\Request\Request;
use PhpSoftBox\CliApp\Response;

use function explode;
use function implode;

use const PHP_EOL;

final readonly class DefaultErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        private UsageFormatter $usageFormatter = new UsageFormatter(),
    ) {
    }

    public function invalidInput(CommandDefinition $definition, Request $request, RunnerInterface $runner): Response
    {
        foreach ($request->errors() as $error) {
            $runner->io()->writeln($error, 'error');
        }

        $help = $this->usageFormatter->formatHelp($definition);
        foreach (explode(PHP_EOL, $help) as $line) {
            $runner->io()->writeln($line, $line === '' ? 'info' : 'comment');
        }

        return new Response(Response::INVALID_INPUT);
    }

    public function unknownCommand(string $command, RunnerInterface $runner): Response
    {
        $runner->io()->writeln("Команда '{$command}' не найдена", 'error');

        return new Response(Response::FAILURE);
    }

    public function showHelp(CommandDefinition $definition, RunnerInterface $runner): Response
    {
        $help = $this->usageFormatter->formatHelp($definition);
        foreach (explode(PHP_EOL, $help) as $line) {
            $runner->io()->writeln($line, $line === '' ? 'info' : 'comment');
        }

        return new Response(Response::SUCCESS);
    }

    public function environmentNotAllowed(CommandDefinition $definition, string $env, RunnerInterface $runner): Response
    {
        $allowed = $definition->environments;
        $runner->io()->writeln(
            "Команда '{$definition->name}' недоступна в окружении '{$env}'. Разрешено: " . implode(', ', $allowed),
            'error',
        );

        return new Response(Response::FAILURE);
    }
}
