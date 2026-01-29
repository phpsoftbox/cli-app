<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Command;

use PhpSoftBox\CliApp\Response;
use RuntimeException;

final class DaemonStartupException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $responseCode = Response::FAILURE,
    ) {
        parent::__construct($message);
    }

    public function responseCode(): int
    {
        return $this->responseCode;
    }
}
