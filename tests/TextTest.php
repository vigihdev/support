<?php

declare(strict_types=1);

namespace Vigihdev\Support\Tests;

use Vigihdev\Support\Text;
use PHPUnit\Framework\TestCase;

final class TextTest extends TestCase
{
    /**
     * @dataProvider toTitleCaseDataProvider
     */
    public function testToTitleCase(string $string, string $expected): void
    {
        self::assertSame($expected, Text::toTitleCase($string));
    }

    public static function toTitleCaseDataProvider(): array
    {
        return [
            'empty string' => ['', ''],
            'single word' => ['hello', 'Hello'],
            'multiple words' => ['hello world', 'Hello World'],
            'all caps' => ['HELLO WORLD', 'Hello World'],
            'mixed case' => ['hELLo wORLd', 'Hello World'],
        ];
    }

    /**
     * @dataProvider toKebabCaseDataProvider
     */
    public function testToKebabCase(string $string, string $expected): void
    {
        self::assertSame($expected, Text::toKebabCase($string));
    }

    public static function toKebabCaseDataProvider(): array
    {
        return [
            'empty string' => ['', ''],
            'single word' => ['hello', 'hello'],
            'with spaces' => ['hello world', 'hello world'],
            'with underscore' => ['hello_world', 'hello-world'],
            'with multiple underscores' => ['hello__world', 'hello-world'],
            'with leading spaces' => ['  hello world', 'hello world'],
        ];
    }

    /**
     * @dataProvider toSnakeCaseDataProvider
     */
    public function testToSnakeCase(string $string, string $expected): void
    {
        self::assertSame($expected, Text::toSnakeCase($string));
    }

    public static function toSnakeCaseDataProvider(): array
    {
        return [
            'empty string' => ['', ''],
            'single word' => ['hello', 'hello'],
            'camelCase' => ['helloWorld', 'hello_world'],
            'PascalCase' => ['HelloWorld', 'hello_world'],
            'with spaces' => ['hello world', 'hello_world'],
            'with hyphens' => ['hello-world', 'hello_world'],
        ];
    }

    /**
     * @dataProvider toCamelCaseDataProvider
     */
    public function testToCamelCase(string $string, string $expected): void
    {
        self::assertSame($expected, Text::toCamelCase($string));
    }

    public static function toCamelCaseDataProvider(): array
    {
        return [
            'empty string' => ['', ''],
            'single word' => ['hello', 'hello'],
            'snake_case' => ['hello_world', 'helloWorld'],
            'PascalCase' => ['HelloWorld', 'helloWorld'],
            'with spaces' => ['hello world', 'helloWorld'],
            'with hyphens' => ['hello-world', 'helloWorld'],
        ];
    }

    /**
     * @dataProvider toPascalCaseDataProvider
     */
    public function testToPascalCase(string $string, string $expected): void
    {
        self::assertSame($expected, Text::toPascalCase($string));
    }

    public static function toPascalCaseDataProvider(): array
    {
        return [
            'empty string' => ['', ''],
            'single word' => ['hello', 'Hello'],
            'camelCase' => ['helloWorld', 'HelloWorld'],
            'snake_case' => ['hello_world', 'HelloWorld'],
            'with spaces' => ['hello world', 'HelloWorld'],
            'with hyphens' => ['hello-world', 'HelloWorld'],
        ];
    }

    /**
     * @dataProvider slugifyDataProvider
     */
    public function testSlugify(string $string, string $expected): void
    {
        self::assertSame($expected, Text::slugify($string));
    }

    public static function slugifyDataProvider(): array
    {
        return [
            'empty string' => ['', ''],
            'simple string' => ['hello world', 'hello-world'],
            'with special chars' => ['hello world!@#', 'hello-world'],
            'with leading/trailing hyphens' => ['-hello-world-', 'hello-world'],
            'unicode string' => ['你好 世界', 'ni-hao-shi-jie'],
        ];
    }

    public function testTruncate(): void
    {
        self::assertSame('hello...', Text::truncate('hello world', 8));
        self::assertSame('hello world', Text::truncate('hello world', 20));
        self::assertSame('hello---', Text::truncate('hello world', 8, '---'));
    }

    public function testContains(): void
    {
        self::assertTrue(Text::contains('hello world', 'world'));
        self::assertFalse(Text::contains('hello world', 'galaxy'));
        self::assertTrue(Text::contains('hello world', ' '));
    }

    public function testRandom(): void
    {
        self::assertIsString(Text::random());
        self::assertEquals(20, strlen(Text::random(10))); // bin2hex doubles the length
        self::assertEquals(32, strlen(Text::random(16)));
    }

    /**
     * @dataProvider toReadableLabelDataProvider
     */
    public function testToReadableLabel(string $string, string $expected): void
    {
        self::assertSame($expected, Text::toReadableLabel($string));
    }

    public static function toReadableLabelDataProvider(): array
    {
        return [
            'camelCase' => ['helloWorld', 'Hello World'],
            'snake_case' => ['hello_world', 'Hello World'],
            'PascalCase' => ['HelloWorld', 'Hello World'],
            'single word' => ['hello', 'Hello'],
        ];
    }
}