<?php

declare(strict_types=1);

namespace CliApp\tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Test for bash completion script
 *
 * This test verifies that the bash completion script exists and is properly formatted.
 */
final class BashCompletionTest extends TestCase
{
    /**
     * Проверим, что скрипт автокомплита существует в директории bin.
     *
     * @see bin/_psb_completion
     */
    #[Test]
    public function completionScriptExists(): void
    {
        $completionScript = __DIR__ . '/../bin/_psb_completion';

        $this->assertFileExists($completionScript, 'Bash completion script should exist');
    }

    /**
     * Проверим, что скрипт автокомплита доступен для чтения.
     *
     * @see bin/_psb_completion
     */
    #[Test]
    public function completionScriptIsReadable(): void
    {
        $completionScript = __DIR__ . '/../bin/_psb_completion';

        $this->assertIsReadable($completionScript, 'Bash completion script should be readable');
    }

    /**
     * Проверим, что скрипт имеет правильный shebang для bash.
     *
     * @see bin/_psb_completion
     */
    #[Test]
    public function completionScriptHasValidBashShebang(): void
    {
        $completionScript = __DIR__ . '/../bin/_psb_completion';
        $content = file_get_contents($completionScript);

        $this->assertStringStartsWith('#!/usr/bin/env bash', $content, 'Script should have bash shebang');
    }

    /**
     * Проверим, что скрипт определяет функцию автокомплита и регистрирует её.
     *
     * @see bin/_psb_completion
     */
    #[Test]
    public function completionScriptDefinesCompletionFunction(): void
    {
        $completionScript = __DIR__ . '/../bin/_psb_completion';
        $content = file_get_contents($completionScript);

        // Скрипт должен определять функцию _psb_completion
        $this->assertStringContainsString('_psb_completion()', $content, 'Script should define _psb_completion function');

        // И регистрировать её через complete command
        $this->assertStringContainsString('complete -o bashdefault -o default -o nospace -F _psb_completion psb', $content, 'Script should register completion');
    }

    /**
     * Проверим, что скрипт проверяет несколько возможных путей к бинарному файлу psb.
     *
     * @see bin/_psb_completion
     */
    #[Test]
    public function completionScriptHandlesVariousPathConfigurations(): void
    {
        $completionScript = __DIR__ . '/../bin/_psb_completion';
        $content = file_get_contents($completionScript);

        // Скрипт должен проверять несколько возможных расположений psb
        $this->assertStringContainsString('command -v psb', $content);
        $this->assertStringContainsString('vendor/bin/psb', $content);
        $this->assertStringContainsString('./bin/psb', $content);
    }
}

