<?php

declare(strict_types=1);

namespace Vigihdev\Support;

use Closure;

final class Arr
{
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array  $array
     * @param  string|array|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get(array|object $array, string|array|null $key, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $array;
        }

        if (is_array($key)) {
            return static::get($array, implode('.', $key), $default);
        }

        // Handle direct key access for both array and object
        if (static::exists($array, $key)) {
            return is_array($array) ? $array[$key] : $array->{$key};
        }

        // Handle dot notation for nested access
        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } elseif (is_object($array) && static::exists($array, $segment)) {
                $array = $array->{$segment};
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param  array  $array
     * @param  string|array  $keys
     * @return bool
     */
    public static function has(array $array, string|array $keys): bool
    {
        if (is_string($keys)) {
            $keys = [$keys];
        }

        if (empty($array) || empty($keys)) {
            return false;
        }

        foreach ($keys as $key) {
            $subArray = $array;
            $segments = explode('.', $key);

            foreach ($segments as $segment) {
                if (is_array($subArray) && array_key_exists($segment, $subArray)) {
                    $subArray = $subArray[$segment];
                } elseif (is_object($subArray) && isset($subArray->{$segment})) {
                    $subArray = $subArray->{$segment};
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Pluck an array of values from an array.
     *
     * @param  array  $array
     * @param  string|array  $value
     * @param  string|array|null  $key
     * @return array
     */
    public static function pluck(array $array, string|array $value, string|array|null $key = null): array
    {
        $results = [];

        [$value, $key] = static::explodePluckParameters($value, $key);

        foreach ($array as $item) {
            $itemValue = static::get($item, $value);

            // If the key is "null", we will just add the value to the array and move along.
            // Otherwise, we will use the key as the array key of the results array.
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = static::get($item, $key);

                if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
                    $itemKey = (string) $itemKey;
                }

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    public static function only(array $array, array|string $keys): array
    {
        $results = [];

        $keys = (array) $keys;

        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                $results[$key] = $array[$key];
            }
        }

        return $results;
    }

    /**
     * Get all of the given array except for a specified array of keys.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    public static function except(array $array, array|string $keys): array
    {
        static::forget($array, $keys);

        return $array;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return void
     */
    public static function forget(array &$array, array|string $keys): void
    {
        $original = &$array;

        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (static::exists($array, $key)) {
                unset($array[$key]);

                continue;
            }

            $parts = explode('.', $key);

            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param  array  $array
     * @param  string|int  $key
     * @return bool
     */
    public static function exists(array|object $array, string|int $key): bool
    {
        if (is_array($array)) {
            return array_key_exists($key, $array);
        }

        if (is_object($array)) {
            return property_exists($array, $key);
        }

        return false;
    }

    /**
     * Get the first element of an array.
     *
     * @param  array  $array
     * @param  (callable(mixed $value, mixed $key): bool)|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public static function first(array $array, ?Closure $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            return empty($array) ? $default : reset($array);
        }

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Get the last element from an array.
     *
     * @param  array  $array
     * @param  (callable(mixed $value, mixed $key): bool)|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public static function last(array $array, ?Closure $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            return empty($array) ? $default : end($array);
        }

        return static::first(array_reverse($array, true), $callback ?? null, $default);
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array  $array
     * @param  string  $prepend
     * @return array
     */
    public static function dot(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, static::dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  array  $array
     * @param  int  $depth
     * @return array
     */
    public static function flatten(array $array, float $depth = INF): array
    {
        $result = [];

        foreach ($array as $item) {
            if (! is_array($item)) {
                $result[] = $item;
            } elseif ($depth <= 1.0) {
                foreach ($item as $subItem) {
                    $result[] = $subItem;
                }
            } else {
                $result = array_merge($result, static::flatten($item, $depth - 1));
            }
        }

        return $result;
    }

    /**
     * Wrap an item in an array if it's not already an array.
     *
     * @param  mixed  $value
     * @return array
     */
    public static function wrap(mixed $value): array
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }

    /**
     * Explode the "value" and "key" arguments passed to the "pluck" method.
     *
     * @param  string|array  $value
     * @param  string|array|null  $key
     * @return array
     */
    protected static function explodePluckParameters(string|array $value, string|array|null $key): array
    {
        $value = is_string($value) ? explode('.', $value) : $value;

        $key = is_string($key) ? explode('.', $key) : $key;

        return [$value, $key];
    }
}
