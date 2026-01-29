<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Runner;

use PhpSoftBox\CliApp\CliApp;
use PhpSoftBox\CliApp\Command\CommandDefinition;
use PhpSoftBox\CliApp\Command\GlobalOptionsProviderInterface;
use PhpSoftBox\CliApp\Command\HandlerInterface;
use PhpSoftBox\CliApp\Events\EventDispatcherInterface;
use PhpSoftBox\CliApp\Events\Events;
use PhpSoftBox\CliApp\Io\IoInterface;
use PhpSoftBox\CliApp\Request\Request;
use PhpSoftBox\CliApp\Request\RequestParser;
use PhpSoftBox\CliApp\Response;

use function getenv;
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

        $signature            = $this->buildSignature($definition);
        $this->currentRequest = RequestParser::parse($signature, $argv);

        if ($this->currentRequest->option('help') === true) {
            return $this->errorHandler->showHelp($definition, $this);
        }

        if ($this->currentRequest->hasErrors()) {
            $resp = $this->errorHandler->invalidInput($definition, $this->currentRequest, $this);
            $this->events->dispatch(Events::ERROR, ['command' => $definition, 'response' => $resp]);

            return $resp;
        }

        $env = (string) $this->currentRequest->option('environment', getenv('APP_ENV') ?: 'production');
        if ($definition->environments !== [] && !in_array($env, $definition->environments, true)) {
            $resp = $this->errorHandler->environmentNotAllowed($definition, $env, $this);
            $this->events->dispatch(Events::ERROR, ['command' => $definition, 'response' => $resp]);

            return $resp;
        }

        try {
            $result = $this->invokeHandler($this->app->resolveHandler($definition));
            $resp   = $this->normalizeResponse($result);
            $this->events->dispatch(Events::AFTER_RUN, ['command' => $definition, 'response' => $resp]);

            return $resp;
        } finally {
            // Восстанавливаем исходный request (для вложенных вызовов).
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

    private function buildSignature(CommandDefinition $definition): \PhpSoftBox\CliApp\Command\Signature
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
