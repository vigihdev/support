<?php

declare(strict_types=1);

namespace Vigihdev\Support\Tests;

use Vigihdev\Support\Collection;
use PHPUnit\Framework\TestCase;
use ArrayIterator;

final class CollectionTest extends TestCase
{
    public function testConstructAndCount(): void
    {
        $collection = new Collection([1, 2, 3]);
        self::assertEquals(3, $collection->count());

        $emptyCollection = new Collection();
        self::assertEquals(0, $emptyCollection->count());
    }

    public function testIsEmpty(): void
    {
        $collection = new Collection([1]);
        self::assertFalse($collection->isEmpty());

        $emptyCollection = new Collection();
        self::assertTrue($emptyCollection->isEmpty());
    }

    public function testGetIterator(): void
    {
        $data = ['a' => 1, 'b' => 2];
        $collection = new Collection($data);
        $iterator = $collection->getIterator();

        self::assertInstanceOf(ArrayIterator::class, $iterator);
        self::assertEquals($data, $iterator->getArrayCopy());
    }

    public function testFilter(): void
    {
        $collection = new Collection([1, 2, 3, 4]);
        $filtered = $collection->filter(fn($value) => $value > 2);

        self::assertNotSame($collection, $filtered);
        self::assertEquals([3, 4], $filtered->values());
        self::assertEquals([1, 2, 3, 4], $collection->toArray()); // Original unchanged
    }

    public function testMap(): void
    {
        $collection = new Collection([1, 2, 3]);
        $mapped = $collection->map(fn($value) => $value * 2);

        self::assertNotSame($collection, $mapped);
        self::assertEquals([2, 4, 6], $mapped->toArray());
        self::assertEquals([1, 2, 3], $collection->toArray()); // Original unchanged
    }

    public function testReduce(): void
    {
        $collection = new Collection([1, 2, 3]);
        $sum = $collection->reduce(fn($carry, $item) => $carry + $item, 0);

        self::assertEquals(6, $sum);
    }

    public function testFirstAndLast(): void
    {
        $collection = new Collection(['a', 'b', 'c']);
        self::assertEquals('a', $collection->first());
        self::assertEquals('c', $collection->last());

        $emptyCollection = new Collection();
        self::assertNull($emptyCollection->first());
        self::assertNull($emptyCollection->last());
    }

    public function testToArray(): void
    {
        $data = [1, 2, 3];
        $collection = new Collection($data);
        self::assertEquals($data, $collection->toArray());
    }

    public function testJsonConversion(): void
    {
        $data = ['a' => 1, 'b' => 2];
        $collection = new Collection($data);
        $json = "{\n    \"a\": 1,\n    \"b\": 2\n}";

        self::assertEquals($data, $collection->jsonSerialize());
        self::assertEquals(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), $collection->toJson());
        self::assertEquals($collection->toJson(), (string) $collection);
    }

    public function testMutableMethods(): void
    {
        $collection = new Collection(['a' => 1]);

        // Test set (update and add)
        $collection->set('a', 10)->set('b', 20);
        self::assertEquals(['a' => 10, 'b' => 20], $collection->toArray());

        // Test add
        $collection->add(30);
        self::assertEquals(['a' => 10, 'b' => 20, 0 => 30], $collection->toArray());

        // Test remove
        $collection->remove('b');
        self::assertEquals(['a' => 10, 0 => 30], $collection->toArray());

        // Test clear
        $collection->clear();
        self::assertTrue($collection->isEmpty());
    }

    public function testGetAndHas(): void
    {
        $collection = new Collection(['a' => 1, 'b' => null]);

        self::assertTrue($collection->has('a'));
        self::assertTrue($collection->has('b')); // key exists even if value is null
        self::assertFalse($collection->has('c'));

        self::assertEquals(1, $collection->get('a'));
        self::assertEquals(null, $collection->get('b'));
        self::assertEquals('default', $collection->get('c', 'default'));
    }

    public function testKeysAndValues(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        self::assertEquals(['a', 'b'], $collection->keys());
        self::assertEquals([1, 2], $collection->values());
    }

    public function testMerge(): void
    {
        $collection1 = new Collection(['a' => 1]);
        $collection2 = new Collection(['b' => 2]);

        $merged = $collection1->merge($collection2);
        self::assertNotSame($collection1, $merged);
        self::assertEquals(['a' => 1, 'b' => 2], $merged->toArray());

        $mergedWithArray = $collection1->merge(['c' => 3]);
        self::assertEquals(['a' => 1, 'c' => 3], $mergedWithArray->toArray());
    }

    public function testSlice(): void
    {
        $collection = new Collection(['a', 'b', 'c', 'd']);
        $slice = $collection->slice(1, 2);

        self::assertNotSame($collection, $slice);
        self::assertEquals([1 => 'b', 2 => 'c'], $slice->toArray());
    }

    public function testChunk(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        $chunks = $collection->chunk(2);

        self::assertInstanceOf(Collection::class, $chunks);
        self::assertEquals(3, $chunks->count());
        self::assertEquals([[0 => 1, 1 => 2], [2 => 3, 3 => 4], [4 => 5]], $chunks->toArray());
    }

    public function testPluck(): void
    {
        $collection = new Collection([
            ['product' => 'Desk', 'price' => 200],
            ['product' => 'Chair', 'price' => 100],
            ['product' => 'Bookcase'],
        ]);

        $plucked = $collection->pluck('price');
        self::assertEquals([200, 100, null], $plucked->toArray());
    }

    public function testGroupBy(): void
    {
        $collection = new Collection([
            ['account_id' => 'acc-1', 'product' => 'Desk'],
            ['account_id' => 'acc-1', 'product' => 'Chair'],
            ['account_id' => 'acc-2', 'product' => 'Bookcase'],
        ]);

        $grouped = $collection->groupBy('account_id');
        self::assertCount(2, $grouped);
        self::assertCount(2, $grouped->get('acc-1'));
        self::assertCount(1, $grouped->get('acc-2'));
    }

    public function testSort(): void
    {
        $collection = new Collection([5, 3, 1, 4, 2]);
        $sorted = $collection->sort();

        self::assertNotSame($collection, $sorted);
        self::assertEquals([1, 2, 3, 4, 5], $sorted->values());

        // Custom sort
        $users = new Collection([
            ['name' => 'C', 'age' => 20],
            ['name' => 'A', 'age' => 30],
            ['name' => 'B', 'age' => 10],
        ]);
        $sortedUsers = $users->sort(fn($a, $b) => $a['age'] <=> $b['age']);
        self::assertEquals('B', $sortedUsers->first()['name']);
    }

    public function testReverse(): void
    {
        $collection = new Collection(['a', 'b', 'c']);
        $reversed = $collection->reverse();

        self::assertNotSame($collection, $reversed);
        self::assertEquals(['c', 'b', 'a'], $reversed->values());
    }
}
