<?php

namespace Twist\Library\Util;

use Twist\Library\Data\Collection;

/**
 * Class Arr
 *
 * @package Twist\Library\Util
 */
class Arr
{

    use Macro;

    /**
     * Determine whether the given value is array accessible.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function accessible($value): bool
    {
        return is_array($value) || $value instanceof \ArrayAccess;
    }

    /**
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @param array $array
     *
     * @return array
     */
    public static function divide(array $array): array
    {
        return [array_keys($array), array_values($array)];
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param array  $array
     * @param string $prepend
     *
     * @return array
     */
    public static function dot(array $array, string $prepend = ''): array
    {
        $results = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && static::isAssoc($value)) {
                $results = array_merge($results, static::dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * Fill the target array with the source values.
     *
     * @param array $target
     * @param array $source
     *
     * @return array
     */
    public static function fill(array $target, array $source): array
    {
        $values = Arr::dot($source);

        foreach ($values as $key => $value) {
            if (static::has($target, $key, true)) {
                static::set($target, $key, $value);
            }
        }

        return $target;
    }

    /**
     * Get all of the given array except for a specified array of items.
     *
     * @param array        $array
     * @param array|string $keys
     *
     * @return array
     */
    public static function except(array $array, $keys): array
    {
        static::forget($array, $keys);

        return $array;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param \ArrayAccess|array $array
     * @param string|int         $key
     *
     * @return bool
     */
    public static function exists($array, $key): bool
    {
        if ($array instanceof \ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param array         $array
     * @param callable|null $callback
     * @param mixed         $default
     *
     * @return mixed
     */
    public static function first(array $array, callable $callback = null, $default = null)
    {
        if ($callback === null) {
            return empty($array) ? Data::value($default) : reset($array);
        }

        foreach ($array as $key => $value) {
            if ($callback($key, $value)) {
                return $value;
            }
        }

        return Data::value($default);
    }

    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param array         $array
     * @param callable|null $callback
     * @param mixed         $default
     *
     * @return mixed
     */
    public static function last(array $array, callable $callback = null, $default = null)
    {
        if ($callback === null) {
            return empty($array) ? Data::value($default) : end($array);
        }

        return static::first(array_reverse($array), $callback, $default);
    }

    /**
     * Collapse an array of arrays into a single array.
     *
     * @param array $array
     *
     * @return array
     */
    public static function collapse(array $array): array
    {
        $results = [];

        foreach ($array as $values) {
            if ($values instanceof Collection) {
                $values = $values->all();
            } elseif (!is_array($values)) {
                continue;
            }

            $results = array_merge($results, $values);
        }

        return $results;
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param array $array
     * @param int   $depth
     *
     * @return array
     */
    public static function flatten(array $array, int $depth = 999): array
    {
        $result = [];

        foreach ($array as $item) {
            $item = $item instanceof Collection ? $item->all() : $item;

            if (is_array($item)) {
                if ($depth === 1) {
                    $result = array_merge($result, $item);
                    continue;
                }

                $result = array_merge($result, static::flatten($item, $depth - 1));
                continue;
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    public static function set(array &$array, string $key, $value): array
    {
        if ($key === null) {
            $array = $value;

            return $array;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Add an element to an array using "dot" notation if it doesn't exist.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    public static function add(array $array, string $key, $value): array
    {
        if (!static::has($array, $key)) {
            static::set($array, $key, $value);
        }

        return $array;
    }

    /**
     * Check if an item exists in an array using "dot" notation.
     *
     * @param array  $array
     * @param string $key
     * @param bool   $relaxed
     *
     * @return bool
     */
    public static function has(array $array, string $key, $relaxed = false): bool
    {
        if (!$array) {
            return false;
        }

        if ($key === null) {
            return false;
        }

        if (static::exists($array, $key)) {
            return true;
        }

        foreach (explode('.', $key) as $segment) {
            if ($segment === '*') {
                return true;
            }

            if (!static::accessible($array)) {
                return false;
            }

            if (static::exists($array, $segment)) {
                $array = $array[$segment];
            } elseif ($relaxed && !static::isAssoc($array)) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function get(array $array, string $key, $default = null)
    {
        if (!static::accessible($array)) {
            return Data::value($default);
        }

        if ($key === null) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return Data::value($default);
            }
        }

        return $array;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param array        $array
     * @param array|string $keys
     *
     * @return array
     */
    public static function forget(array &$array, $keys): array
    {
        $original = &$array;
        $keys     = (array)$keys;

        if (count($keys) === 0) {
            return $array;
        }

        foreach ((array)$keys as $key) {
            if (static::exists($array, $key)) {
                unset($array[$key]);
                continue;
            }

            $parts = explode('.', $key);
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

        return $array;
    }

    /**
     * Determines if an array is associative.
     *
     * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
     *
     * @param array $array
     *
     * @return bool
     */
    public static function isAssoc(array $array): bool
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param array        $array
     * @param array|string $keys
     *
     * @return array
     */
    public static function only(array $array, $keys): array
    {
        return array_intersect_key($array, array_flip((array)$keys));
    }

    /**
     * Pluck an array of values from an array.
     *
     * @param array             $array
     * @param string|array      $value
     * @param string|array|null $key
     *
     * @return array
     */
    public static function pluck(array $array, $value, $key = null): array
    {
        $results = [];

        list($value, $key) = Arr::explodePluckParameters($value, $key);

        foreach ($array as $item) {
            $itemValue = Data::get($item, $value);

            if ($key === null) {
                $results[] = $itemValue;
            } else {
                $itemKey = Data::get($item, $key);

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    /**
     * Explode the "value" and "key" arguments passed to "pluck".
     *
     * @param string|array      $value
     * @param string|array|null $key
     *
     * @return array
     */
    protected static function explodePluckParameters($value, $key): array
    {
        $value = is_string($value) ? explode('.', $value) : $value;
        $key   = ($key === null) || is_array($key) ? $key : explode('.', $key);

        return [$value, $key];
    }

    /**
     * Push an item onto the beginning of an array.
     *
     * @param array $array
     * @param mixed $value
     * @param mixed $key
     *
     * @return array
     */
    public static function prepend(array $array, $value, $key = null): array
    {
        if ($key === null) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }

    /**
     * Get a value from the array, and remove it.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function pull(array &$array, string $key, $default = null)
    {
        $value = static::get($array, $key, $default);
        static::forget($array, $key);

        return $value;
    }

    /**
     * Sort the array using the given callback.
     *
     * @param array    $array
     * @param callable $callback
     *
     * @return array
     */
    public static function sort(array $array, callable $callback): array
    {
        return Collection::make($array)->sortBy($callback)->all();
    }

    /**
     * Recursively sort an array by keys and values.
     *
     * @param array $array
     *
     * @return array
     */
    public static function sortRecursive(array $array): array
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = static::sortRecursive($value);
            }
        }

        unset ($value);

        if (static::isAssoc($array)) {
            ksort($array);
        } else {
            sort($array);
        }

        return $array;
    }

    /**
     * Filter the array using the given callback.
     *
     * @param array    $array
     * @param callable $callback
     *
     * @return array
     */
    public static function where(array $array, callable $callback): array
    {
        $filtered = [];

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function value(array $array, string $key, $default = null)
    {
        return static::has($array, $key) ? static::get($array, $key) : Data::value($default);
    }

    /**
     * @param array $array
     *
     * @return array
     */
    public static function values(array $array): array
    {
        return array_filter(array_map(function ($item) {
            return Data::value($item);
        }, $array));
    }

    /**
     * Results array of items.
     *
     * @param mixed $items
     *
     * @return array
     */
    public static function items($items): array
    {
        if (is_array($items)) {
            return $items;
        }

        if ($items instanceof Collection) {
            $items = $items->all();
        } elseif (method_exists($items, 'toArray')) {
            $items = $items->toArray();
        } elseif (method_exists($items, 'toJson')) {
            $items = json_decode($items->toJson(), true);
        } elseif ($items instanceof \Traversable) {
            return iterator_to_array($items);
        }

        return (array)$items;
    }

    /**
     * @param array  $array
     * @param string $glue
     *
     * @return string
     */
    public static function implode(array $array, string $glue = ''): string
    {
        $result = Arr::flatten($array);

        return implode($glue, Arr::values($result));
    }

    /**
     * @param array    $array
     * @param callable $callback
     *
     * @return array
     */
    public static function map(array $array, callable $callback): array
    {
        $keys  = array_keys($array);
        $items = array_map($callback, $keys, $array);

        return array_combine($keys, $items);
    }

}