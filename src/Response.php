<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp;

final class Response
{
    public const SUCCESS       = 0;
    public const FAILURE       = 1;
    public const INVALID_INPUT = 2;

    public function __construct(
        public readonly int $code = self::SUCCESS,
        public readonly ?string $message = null,
        public readonly mixed $data = null,
    ) {
    }
}
