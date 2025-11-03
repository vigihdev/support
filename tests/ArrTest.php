<?php

declare(strict_types=1);

namespace Vigihdev\Support\Tests;

use Vigihdev\Support\Arr;
use PHPUnit\Framework\TestCase;

final class ArrTest extends TestCase
{
    public function testGet(): void
    {
        $array = [
            'name' => 'John Doe',
            'age' => 30,
            'address' => [
                'street' => '123 Main St',
                'city' => 'Anytown',
                'zip' => '12345',
            ],
            'emails' => [
                'personal' => 'john@example.com',
                'work' => 'john.work@example.com',
            ],
            'null_value' => null,
        ];

        // Basic get
        self::assertEquals('John Doe', Arr::get($array, 'name'));
        self::assertEquals(30, Arr::get($array, 'age'));

        // Dot notation
        self::assertEquals('123 Main St', Arr::get($array, 'address.street'));
        self::assertEquals('john@example.com', Arr::get($array, 'emails.personal'));

        // Default value
        self::assertNull(Arr::get($array, 'non_existent'));
        self::assertEquals('default', Arr::get($array, 'non_existent', 'default'));

        // Null value exists
        self::assertNull(Arr::get($array, 'null_value'));
        self::assertNull(Arr::get($array, 'null_value', 'default')); // Should still return null, not default

        // Get entire array
        self::assertEquals($array, Arr::get($array, null));

        // Get with array key
        self::assertEquals('123 Main St', Arr::get($array, ['address', 'street']));
    }

    public function testHas(): void
    {
        $array = [
            'name' => 'John Doe',
            'address' => [
                'street' => '123 Main St',
                'city' => 'Anytown',
            ],
            'null_value' => null,
        ];

        // Basic has
        self::assertTrue(Arr::has($array, 'name'));
        self::assertFalse(Arr::has($array, 'age'));

        // Dot notation
        self::assertTrue(Arr::has($array, 'address.street'));
        self::assertFalse(Arr::has($array, 'address.zip'));

        // Multiple keys
        self::assertTrue(Arr::has($array, ['name', 'address.city']));
        self::assertFalse(Arr::has($array, ['name', 'address.zip']));

        // Null value exists
        self::assertTrue(Arr::has($array, 'null_value'));

        // Empty array or keys
        self::assertFalse(Arr::has([], 'name'));
        self::assertFalse(Arr::has($array, []));
    }

    public function testPluck(): void
    {
        $array = [
            ['user' => ['id' => 1, 'name' => 'John'], 'role' => 'Admin'],
            ['user' => ['id' => 2, 'name' => 'Jane'], 'role' => 'Editor'],
            ['user' => ['id' => 3, 'name' => 'Doe']],
        ];

        // Pluck values
        self::assertEquals(['John', 'Jane', 'Doe'], Arr::pluck($array, 'user.name'));

        // Pluck values with keys
        self::assertEquals([1 => 'John', 2 => 'Jane', 3 => 'Doe'], Arr::pluck($array, 'user.name', 'user.id'));

        // Pluck non-existent value
        self::assertEquals([null, null, null], Arr::pluck($array, 'user.email'));

        // Pluck with object as key
        $objectKeyArray = [
            (object)['id' => 1, 'name' => 'A'],
            (object)['id' => 2, 'name' => 'B'],
        ];
        self::assertEquals([1 => 'A', 2 => 'B'], Arr::pluck($objectKeyArray, 'name', 'id'));
    }

    public function testOnly(): void
    {
        $array = ['name' => 'John', 'age' => 30, 'city' => 'New York'];

        self::assertEquals(['name' => 'John', 'age' => 30], Arr::only($array, ['name', 'age']));
        self::assertEquals(['name' => 'John'], Arr::only($array, 'name'));
        self::assertEquals([], Arr::only($array, ['non_existent']));
    }

    public function testExcept(): void
    {
        $array = ['name' => 'John', 'age' => 30, 'city' => 'New York'];

        self::assertEquals(['city' => 'New York'], Arr::except($array, ['name', 'age']));
        self::assertEquals(['age' => 30, 'city' => 'New York'], Arr::except($array, 'name'));
        self::assertEquals($array, Arr::except($array, ['non_existent']));
    }

    public function testForget(): void
    {
        $array = [
            'name' => 'John',
            'user' => [
                'address' => [
                    'street' => 'Main',
                    'zip' => '123',
                ],
            ],
            'data' => [
                'items' => [
                    'item1' => 'value1',
                    'item2' => 'value2',
                ],
            ],
        ];

        // Forget top-level key
        Arr::forget($array, 'name');
        self::assertFalse(Arr::has($array, 'name'));

        // Forget nested key
        Arr::forget($array, 'user.address.street');
        self::assertFalse(Arr::has($array, 'user.address.street'));
        self::assertTrue(Arr::has($array, 'user.address.zip'));

        // Forget multiple keys
        Arr::forget($array, ['data.items.item1', 'data.items.item2']);
        self::assertFalse(Arr::has($array, 'data.items.item1'));
        self::assertFalse(Arr::has($array, 'data.items.item2'));

        // Forget non-existent key (should not throw error)
        Arr::forget($array, 'non.existent.key');
        self::assertTrue(true); // If no error, test passes

        // Forget empty keys
        $originalArray = ['a' => 1];
        Arr::forget($originalArray, []);
        self::assertEquals(['a' => 1], $originalArray);
    }

    public function testExists(): void
    {
        $array = ['a' => 1, 'b' => null];

        self::assertTrue(Arr::exists($array, 'a'));
        self::assertTrue(Arr::exists($array, 'b'));
        self::assertFalse(Arr::exists($array, 'c'));
    }

    public function testFirst(): void
    {
        $array = [1, 2, 3, 4, 5];

        self::assertEquals(1, Arr::first($array));
        self::assertEquals(3, Arr::first($array, fn($value) => $value > 2));
        self::assertNull(Arr::first($array, fn($value) => $value > 10));
        self::assertEquals('default', Arr::first($array, fn($value) => $value > 10, 'default'));
        self::assertNull(Arr::first([], fn($value) => $value > 10));
    }

    public function testLast(): void
    {
        $array = [1, 2, 3, 4, 5];

        self::assertEquals(5, Arr::last($array));
        self::assertEquals(3, Arr::last($array, fn($value) => $value < 4));
        self::assertNull(Arr::last($array, fn($value) => $value > 10));
        self::assertEquals('default', Arr::last($array, fn($value) => $value > 10, 'default'));
        self::assertNull(Arr::last([], fn($value) => $value > 10));
    }

    public function testDot(): void
    {
        $array = [
            'user' => [
                'name' => 'John',
                'address' => [
                    'city' => 'New York',
                    'zip' => '10001',
                ],
            ],
            'posts' => [
                ['id' => 1, 'title' => 'Post 1'],
                ['id' => 2, 'title' => 'Post 2'],
            ],
            'status' => 'active',
        ];

        $expected = [
            'user.name' => 'John',
            'user.address.city' => 'New York',
            'user.address.zip' => '10001',
            'posts.0.id' => 1,
            'posts.0.title' => 'Post 1',
            'posts.1.id' => 2,
            'posts.1.title' => 'Post 2',
            'status' => 'active',
        ];

        self::assertEquals($expected, Arr::dot($array));

        $emptyArray = [];
        self::assertEquals([], Arr::dot($emptyArray));
    }

    public function testFlatten(): void
    {
        $array = [
            'name' => 'John',
            'skills' => ['PHP', 'JS'],
            'address' => [
                'city' => 'New York',
                'zip' => '10001',
            ],
            'hobbies' => [
                ['reading', 'coding'],
                'gaming',
            ],
        ];

        self::assertEquals(
            ['John', 'PHP', 'JS', 'New York', '10001', 'reading', 'coding', 'gaming'],
            Arr::flatten($array)
        );

        // Flatten with depth
        $nestedArray = [1, [2, [3, 4]], 5];
        self::assertEquals([1, 2, [3, 4], 5], Arr::flatten($nestedArray, 1));
        self::assertEquals([1, 2, 3, 4, 5], Arr::flatten($nestedArray, 2));
    }

    public function testWrap(): void
    {
        self::assertEquals([1], Arr::wrap(1));
        self::assertEquals(['hello'], Arr::wrap('hello'));
        self::assertEquals(['a' => 1], Arr::wrap(['a' => 1]));
        self::assertEquals([], Arr::wrap(null));
    }
}
