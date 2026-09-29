<?php

declare(strict_types=1);

namespace PhpSoftBox\CliApp\Tests;

use PhpSoftBox\CliApp\Command\ArgumentDefinition;
use PhpSoftBox\CliApp\Command\OptionDefinition;
use PhpSoftBox\CliApp\Command\Signature;
use PhpSoftBox\CliApp\Request\RequestParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RequestParser::class)]
#[CoversClass(Signature::class)]
#[CoversClass(ArgumentDefinition::class)]
#[CoversClass(OptionDefinition::class)]
final class RequestParserTest extends TestCase
{
    /**
     * Проверяет разбор длинных опций и позиционных аргументов.
     */
    #[Test]
    public function testParsesLongOptionsAndArgs(): void
    {
        $signature = new Signature([
            new ArgumentDefinition('name'),
            new OptionDefinition('env', 'e', required: true),
            new OptionDefinition('force', 'f', flag: true),
        ]);

        $req = RequestParser::parse($signature, ['john', '--env=prod', '-f']);

        self::assertSame('john', $req->param('name'));
        self::assertSame('prod', $req->option('env'));
        self::assertTrue($req->option('force'));
        self::assertFalse($req->hasErrors());
    }

    /**
     * Проверяет, что вариативный аргумент собирает все оставшиеся значения.
     */
    #[Test]
    public function testVariadicArgumentCollectsRest(): void
    {
        $signature = new Signature([
            new ArgumentDefinition('files', required: false, variadic: true),
        ]);

        $req = RequestParser::parse($signature, ['a.php', 'b.php', 'c.php']);

        self::assertSame(['a.php', 'b.php', 'c.php'], $req->param('files'));
    }

    /**
     * Проверяет агрегацию repeatable опции.
     */
    #[Test]
    public function testRepeatableOptionAggregatesValues(): void
    {
        $signature = new Signature([
            new OptionDefinition('path', 'p', required: true, repeatable: true),
        ]);

        $req = RequestParser::parse($signature, ['-p', 'one', '-p', 'two']);

        self::assertSame(['one', 'two'], $req->option('path'));
    }

    /**
     * Проверяет ошибку при отсутствии обязательного аргумента.
     */
    #[Test]
    public function testErrorsWhenRequiredArgumentMissing(): void
    {
        $signature = new Signature([
            new ArgumentDefinition('name', required: true),
        ]);

        $req = RequestParser::parse($signature, []);

        self::assertTrue($req->hasErrors());
        self::assertNotEmpty($req->errors());
    }

    /**
     * Проверяет ошибку при отсутствии обязательной опции.
     */
    #[Test]
    public function testErrorsWhenRequiredOptionMissing(): void
    {
        $signature = new Signature([
            new OptionDefinition('env', 'e', required: true),
        ]);

        $req = RequestParser::parse($signature, []);

        self::assertTrue($req->hasErrors());
        self::assertStringContainsString('Обязательная опция', $req->errors()[0]);
    }

    /**
     * Проверяет разбор короткого набора флагов с inline-значением.
     */
    #[Test]
    public function testParsesShortBundleWithInlineValue(): void
    {
        $signature = new Signature([
            new OptionDefinition('all', 'a', flag: true),
            new OptionDefinition('count', 'c', required: true, type: 'int'),
        ]);

        $req = RequestParser::parse($signature, ['-ac5']);

        self::assertTrue($req->option('all'));
        self::assertSame(5, $req->option('count'));
        self::assertFalse($req->hasErrors());
    }

    /**
     * Проверяет ошибку для неизвестной опции.
     */
    #[Test]
    public function testUnknownOptionProducesError(): void
    {
        $signature = new Signature([]);

        $req = RequestParser::parse($signature, ['--nope']);

        self::assertTrue($req->hasErrors());
        self::assertStringContainsString('Неизвестная опция', $req->errors()[0]);
    }

    /**
     * Проверим, что после `--` все токены — позиционные аргументы, даже похожие на опции.
     *
     * @see RequestParser::parse()
     */
    #[Test]
    public function doubleDashEndsOptions(): void
    {
        $signature = new Signature([
            new ArgumentDefinition('first'),
            new ArgumentDefinition('second'),
            new OptionDefinition('force', 'f', flag: true),
        ]);

        $request = RequestParser::parse($signature, ['--', '--force', '-f']);

        self::assertSame([], $request->errors());
        self::assertSame('--force', $request->param('first'));
        self::assertSame('-f', $request->param('second'));
        self::assertFalse($request->option('force'));
    }

    /**
     * Проверим, что отрицательное число — позиционный аргумент, а не короткая опция.
     *
     * @see RequestParser::parse()
     */
    #[Test]
    public function negativeNumberIsArgument(): void
    {
        $request = RequestParser::parse(new Signature([new ArgumentDefinition('offset', type: 'int')]), ['-5']);

        self::assertSame([], $request->errors());
        self::assertSame(-5, $request->param('offset'));
    }

    /**
     * Проверим, что повторяемая опция заменяет значение по умолчанию, а не дописывается к нему.
     *
     * @see RequestParser::parse()
     */
    #[Test]
    public function repeatableOptionReplacesDefault(): void
    {
        $signature = new Signature([new OptionDefinition('tag', 't', default: ['default'], repeatable: true)]);

        self::assertSame(['a', 'b'], RequestParser::parse($signature, ['--tag', 'a', '-t', 'b'])->option('tag'));
        self::assertSame(['default'], RequestParser::parse($signature, [])->option('tag'));
    }
}
