<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Events;

final class Events
{
    public const BEFORE_RUN = 'beforeRun';
    public const AFTER_RUN  = 'afterRun';
    public const ERROR      = 'error';
    public const QUESTION   = 'question';
    public const OUTPUT     = 'output';
}
