<?php

declare(strict_types=1);

namespace Vigihdev\Support;

use ArrayIterator;
use Closure;
use IteratorAggregate;
use JsonSerializable;
use Stringable;
use Vigihdev\Support\Contracts\{ToArrayInterface, ToJsonInterface};

/**
 * Collection
 *
 * Kelas ini menyediakan fungsionalitas untuk mengelola dan memanipulasi array data dengan cara yang lebih ekspresif dan berorientasi objek.
 */
final class Collection implements IteratorAggregate, JsonSerializable, Stringable, ToJsonInterface, ToArrayInterface
{
    protected array $data = [];

    /**
     * Membuat instance Collection baru.
     *
     * @param array $data Data awal untuk koleksi.
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Mendapatkan jumlah item dalam koleksi.
     *
     * @return int Jumlah item.
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Memeriksa apakah koleksi kosong.
     *
     * @return bool True jika koleksi kosong, false jika tidak.
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Mendapatkan iterator untuk koleksi.
     *
     * @return \ArrayIterator Iterator untuk data koleksi.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Filter koleksi dengan closure.
     *
     * @param \Closure(mixed $value, mixed $key): bool $callback Fungsi callback untuk memfilter item.
     * @return self Instance Collection baru dengan item yang sudah difilter.
     */
    public function filter(Closure $callback): self
    {
        $filtered = array_filter($this->data, $callback, ARRAY_FILTER_USE_BOTH);
        return new self($filtered);
    }

    /**
     * Map koleksi dengan closure.
     *
     * @param \Closure(mixed $value, mixed $key): mixed $callback Fungsi callback untuk memetakan item.
     * @return self Instance Collection baru dengan item yang sudah dipetakan.
     */
    public function map(Closure $callback): self
    {
        $mapped = array_map($callback, $this->data, array_keys($this->data));
        return new self($mapped);
    }

    /**
     * Reduce koleksi dengan closure.
     *
     * @param \Closure(mixed $carry, mixed $item): mixed $callback Fungsi callback untuk mereduksi item.
     * @param mixed $initial Nilai awal untuk carry.
     * @return mixed Hasil reduksi.
     */
    public function reduce(Closure $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->data, $callback, $initial);
    }

    /**
     * Mendapatkan elemen pertama dari koleksi.
     *
     * @return mixed Elemen pertama atau null jika koleksi kosong.
     */
    public function first(): mixed
    {
        return $this->data[array_key_first($this->data)] ?? null;
    }

    /**
     * Mendapatkan elemen terakhir dari koleksi.
     *
     * @return mixed Elemen terakhir atau null jika koleksi kosong.
     */
    public function last(): mixed
    {
        return $this->data[array_key_last($this->data)] ?? null;
    }

    /**
     * Mendapatkan semua data koleksi sebagai array.
     *
     * @return array Data koleksi.
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Convert to JSON string
     */
    public function toJson(int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES): string
    {
        return json_encode($this->jsonSerialize(), $flags);
    }

    /**
     * Convert to JSON serializable array
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }

    /**
     * Convert to string (JSON representation)
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Add item to collection
     */
    public function add(mixed $item): self
    {
        $this->data[] = $item;
        return $this;
    }

    /**
     * Set item dengan key tertentu
     */
    public function set(string|int $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Get item by key
     */
    public function get(string|int $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if key exists
     */
    public function has(string|int $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Remove item by key
     */
    public function remove(string|int $key): self
    {
        unset($this->data[$key]);
        return $this;
    }

    /**
     * Get keys
     */
    public function keys(): array
    {
        return array_keys($this->data);
    }

    /**
     * Get values
     */
    public function values(): array
    {
        return array_values($this->data);
    }

    /**
     * Merge dengan array/collection lain
     */
    public function merge(array|self $items): self
    {
        $mergeData = $items instanceof self ? $items->toArray() : $items;
        return new self(array_merge($this->data, $mergeData));
    }

    /**
     * Slice collection
     */
    public function slice(int $offset, ?int $length = null): self
    {
        return new self(array_slice($this->data, $offset, $length, true));
    }

    /**
     * Chunk collection
     */
    public function chunk(int $size): self
    {
        return new self(array_chunk($this->data, $size, true));
    }

    /**
     * Pluck value dari array of arrays
     */
    public function pluck(string $key): self
    {
        return $this->map(fn($item) => $item[$key] ?? null);
    }

    /**
     * Group by key
     */
    public function groupBy(string $key): self
    {
        $grouped = [];
        foreach ($this->data as $item) {
            $groupKey = $item[$key] ?? null;
            $grouped[$groupKey][] = $item;
        }
        return new self($grouped);
    }

    /**
     * Sort collection
     */
    public function sort(?Closure $callback = null): self
    {
        $data = $this->data;
        $callback ? uasort($data, $callback) : asort($data);
        return new self($data);
    }

    /**
     * Reverse collection
     */
    public function reverse(): self
    {
        return new self(array_reverse($this->data, true));
    }

    /**
     * Clear all data
     */
    public function clear(): self
    {
        $this->data = [];
        return $this;
    }
}
