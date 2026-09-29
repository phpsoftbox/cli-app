<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Runner;

use PhpSoftBox\CliApp\CliApp;
use PhpSoftBox\CliApp\Command\CommandDefinition;
use PhpSoftBox\CliApp\Command\DaemonHandlerInterface;
use PhpSoftBox\CliApp\Command\DaemonStartupException;
use PhpSoftBox\CliApp\Command\GlobalOptionsProviderInterface;
use PhpSoftBox\CliApp\Command\HandlerInterface;
use PhpSoftBox\CliApp\Command\Signature;
use PhpSoftBox\CliApp\Events\EventDispatcherInterface;
use PhpSoftBox\CliApp\Events\Events;
use PhpSoftBox\CliApp\Io\IoInterface;
use PhpSoftBox\CliApp\Request\Request;
use PhpSoftBox\CliApp\Request\RequestParser;
use PhpSoftBox\CliApp\Response;
use RuntimeException;
use Throwable;

use function in_array;
use function is_callable;
use function is_int;
use function is_string;

final class Runner implements RunnerInterface
{
    private Request $currentRequest;

    public function __construct(
        private readonly CliApp $app,
        private readonly IoInterface $io,
        private readonly EventDispatcherInterface $events,
        private readonly ErrorHandlerInterface $errorHandler,
    ) {
        $this->currentRequest = new Request([], []);
    }

    public function run(string $command, array $argv): Response
    {
        if ($command === '') {
            $command = 'list';
        }

        $prevRequest = $this->currentRequest;
        $definition  = $this->app->resolveCommand($command);
        if ($definition === null) {
            $resp = $this->errorHandler->unknownCommand($command, $this);
            $this->events->dispatch(Events::ERROR, ['command' => $command, 'response' => $resp]);

            return $resp;
        }

        $this->events->dispatch(Events::BEFORE_RUN, ['command' => $definition, 'argv' => $argv]);

        try {
            $this->currentRequest = RequestParser::parse($this->buildSignature($definition), $argv);

            if ($this->currentRequest->option('help') === true) {
                return $this->errorHandler->showHelp($definition, $this);
            }

            if ($this->currentRequest->hasErrors()) {
                $resp = $this->errorHandler->invalidInput($definition, $this->currentRequest, $this);
                $this->events->dispatch(Events::ERROR, ['command' => $definition, 'response' => $resp]);

                return $resp;
            }

            $env = $this->environment();
            if ($definition->environments !== [] && !in_array($env, $definition->environments, true)) {
                $resp = $this->errorHandler->environmentNotAllowed($definition, $env, $this);
                $this->events->dispatch(Events::ERROR, ['command' => $definition, 'response' => $resp]);

                return $resp;
            }

            try {
                $handler = $this->app->resolveHandler($definition);
                $result  = $definition->asDaemon
                    ? $this->invokeDaemonHandler($handler)
                    : $this->invokeHandler($handler);
            } catch (Throwable $exception) {
                $this->events->dispatch(Events::ERROR, ['command' => $definition, 'exception' => $exception]);

                throw $exception;
            }

            $resp = $this->normalizeResponse($result);
            $this->events->dispatch(Events::AFTER_RUN, ['command' => $definition, 'response' => $resp]);

            return $resp;
        } finally {
            // Исходный request восстанавливается на любом выходе (для вложенных вызовов).
            $this->currentRequest = $prevRequest;
        }
    }

    public function runSubCommand(string $command, array $argv): Response
    {
        $clone                 = clone $this;
        $clone->currentRequest = new Request([], []);

        return $clone->run($command, $argv);
    }

    public function request(): Request
    {
        return $this->currentRequest;
    }

    public function io(): IoInterface
    {
        return $this->io;
    }

    public function environment(): string
    {
        $option = $this->currentRequest->option('environment');

        return is_string($option) && $option !== '' ? $option : $this->app->environment();
    }

    private function invokeHandler(mixed $handler): mixed
    {
        if ($handler instanceof HandlerInterface) {
            return $handler->run($this);
        }

        if (is_callable($handler)) {
            return $handler($this);
        }

        return null;
    }

    private function invokeDaemonHandler(mixed $handler): mixed
    {
        try {
            if ($handler instanceof DaemonHandlerInterface) {
                $handler->runAsDaemon($this);

                return null;
            }

            if (is_callable($handler)) {
                return $handler($this);
            }

            throw new RuntimeException('Daemon command handler must implement ' . DaemonHandlerInterface::class . ' or be callable.');
        } catch (DaemonStartupException $exception) {
            if ($exception->getMessage() !== '') {
                $this->io->writeln($exception->getMessage(), 'error');
            }

            return new Response($exception->responseCode());
        }
    }

    private function normalizeResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        if (is_int($result)) {
            return new Response($result);
        }

        if (is_string($result)) {
            return new Response(Response::SUCCESS, $result);
        }

        return new Response(Response::SUCCESS);
    }

    private function buildSignature(CommandDefinition $definition): Signature
    {
        $signature = $definition->signature;
        $registry  = $this->app->registry();
        if (!$registry instanceof GlobalOptionsProviderInterface) {
            return $signature;
        }

        $existing = $signature->options();
        $shorts   = [];
        foreach ($existing as $opt) {
            if ($opt->short !== null) {
                $shorts[$opt->short] = true;
            }
        }

        foreach ($registry->globalOptions() as $opt) {
            if (isset($existing[$opt->name])) {
                continue;
            }
            if ($opt->short !== null && isset($shorts[$opt->short])) {
                continue;
            }
            $signature = $signature->addOption($opt);
        }

        return $signature;
    }
}
