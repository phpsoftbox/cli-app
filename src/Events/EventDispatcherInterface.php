<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Events;

interface EventDispatcherInterface
{
    public function subscribe(string $event, callable $listener): void;
    public function dispatch(string $event, array $payload = []): void;
}
