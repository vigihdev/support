<?php

declare(strict_types=1);

namespace Vigihdev\Support;

use Symfony\Component\String\UnicodeString;
use Symfony\Component\String\ByteString;

final class Text
{

    /**
     * Convert to Title Case
     */
    public static function toTitleCase(string $text): string
    {
        return (new UnicodeString(strtolower($text)))->title(true)->toString();
    }

    /**
     * Convert to Kebab Case (hello-world)
     */
    public static function toKebabCase(string $text): string
    {

        return (new UnicodeString($text))
            ->trim()
            ->replaceMatches('/[\s]+/', ' ')
            ->replaceMatches('/[_]+/', '-')
            ->replaceMatches('/[\s]+/', '-')
            ->lower()
            ->toString();
    }

    /**
     * Convert to Snake Case (hello_world)
     */
    public static function toSnakeCase(string $text): string
    {
        return (new UnicodeString($text))
            ->snake()
            ->toString();
    }

    /**
     * Convert to Camel Case (helloWorld)
     */
    public static function toCamelCase(string $text): string
    {
        return (new ByteString($text))
            ->camel()
            ->toString();
    }

    /**
     * Convert to Pascal Case (HelloWorld)
     */
    public static function toPascalCase(string $text): string
    {
        return (new UnicodeString($text))->camel()->title()->toString();
    }

    /**
     * Generate slug from text
     */
    public static function slugify(string $text): string
    {
        return (new UnicodeString($text))
            ->lower()
            ->ascii()
            ->replace(' ', '-')
            ->replaceMatches('/[^a-z0-9\-]/', '')
            ->trim('-')
            ->toString();
    }

    /**
     * Truncate text with ellipsis
     */
    public static function truncate(string $text, int $length = 50, string $ellipsis = '...'): string
    {
        return (new UnicodeString($text))
            ->truncate($length, $ellipsis)
            ->toString();
    }

    /**
     * Check if string contains substring
     */
    public static function contains(string $haystack, string $needle): bool
    {
        return (new UnicodeString($haystack))->containsAny($needle);
    }

    /**
     * Generate random string
     */
    public static function random(int $length = 10): string
    {
        return bin2hex(random_bytes($length));
    }

    public static function toReadableLabel(string $text, bool $allWords = true): string
    {
        $string = new UnicodeString($text);

        // Handle camelCase
        $string = $string->camel()->snake();

        $string = $string->replaceMatches('/([a-z])([A-Z])/', '$1_$2');

        return $string
            ->replace('_', ' ')
            ->title($allWords)
            ->toString();
    }
}
