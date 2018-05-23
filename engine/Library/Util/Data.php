<?php

namespace Twist\Library\Util;

use Twist\Library\Data\Collection;

/**
 * Class Data
 *
 * @package Twist\Library\Util
 */
class Data
{

    /**
     * @param mixed  $target
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function fetch($target, string $key, $default = null)
    {
        if (Arr::accessible($target) && Arr::exists($target, $key)) {
            return $target[$key];
        }

        if (\is_object($target) && isset($target->{$key})) {
            return $target->{$key};
        }

        return static::value($default);
    }

    /**
     * @param mixed        $target
     * @param string|array $key
     *
     * @return bool
     */
    public static function exists($target, string $key): bool
    {
        return (bool)static::fetch($target, $key);
    }

    /**
     * @param mixed        $target
     * @param string|array $key
     *
     * @return bool
     */
    public static function has($target, $key): bool
    {
        if (empty($key)) {
            return false;
        }

        $key = \is_array($key) ? $key : explode('.', $key);

        while (($segment = array_shift($key)) !== null) {
            if ($segment === '*') {
                return ($target instanceof Collection || \is_array($target));
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (\is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param mixed        $target
     * @param string|array $key
     * @param mixed        $default
     *
     * @return mixed
     */
    public static function get($target, $key, $default = null)
    {
        if (empty($key)) {
            return $target;
        }

        $key = \is_array($key) ? $key : explode('.', $key);

        while (($segment = array_shift($key)) !== null) {
            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (!\is_array($target)) {
                    return static::value($default);
                }

                $result = Arr::pluck($target, $key);

                return \in_array('*', $key, false) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (\is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return static::value($default);
            }
        }

        return $target;
    }

    /**
     * Set an item on an array or object using dot notation.
     *
     * @param mixed        $target
     * @param string|array $key
     * @param mixed        $value
     * @param bool         $overwrite
     *
     * @return mixed
     */
    public static function set(&$target, $key, $value, $overwrite = true)
    {
        $segments = \is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === '*') {
	        /** @noinspection NotOptimalIfConditionsInspection */
	        if (!Arr::accessible($target)) {
                $target = [];
            }

            if ($segments) {
                foreach ($target as &$inner) {
                    static::set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }

            unset($inner);
        } elseif (Arr::accessible($target)) {
            if ($segments) {
                if (!Arr::exists($target, $segment)) {
                    $target[$segment] = [];
                }

                static::set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || !Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (\is_object($target)) {
            if ($segments) {
                if (!isset($target->{$segment})) {
                    $target->{$segment} = [];
                }

                static::set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || !isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        } else {
            $target = [];

            if ($segments) {
                static::set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }

        return $target;
    }

    /**
     * Fill in data where it's missing.
     *
     * @param mixed        $target
     * @param string|array $key
     * @param mixed        $value
     *
     * @return mixed
     */
    public static function fill(&$target, $key, $value)
    {
        return static::set($target, $key, $value, false);
    }

    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function value($value)
    {
        return static::isCallable($value) ? $value() : $value;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function isCallable($value): bool
    {
        if ($value instanceof \Closure) {
            return true;
        }

        if (\is_object($value) && method_exists($value, '__invoke')) {
            return true;
        }

        return false;
    }

}