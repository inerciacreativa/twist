<?php

namespace Twist\Library\Data;

/**
 * Class Cache
 *
 * @package Twist\Library\Data
 */
class Cache
{

    /**
     * @param string $name
     * @param mixed  $value
     * @param int    $expiration
     */
    public static function push(string $name, $value, int $expiration = 0)
    {
        set_transient($name, $value, $expiration);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public static function pull(string $name)
    {
        return get_transient($name);
    }

    /**
     * @param string    $name
     * @param mixed     $value
     * @param null|bool $autoload
     *
     * @return bool
     */
    public static function set(string $name, $value, bool $autoload = null): bool
    {
        return update_option($name, $value, $autoload);
    }

    /**
     * @param string     $name
     * @param mixed|null $default
     *
     * @return mixed
     */
    public static function get(string $name, $default = null)
    {
        return get_option($name, $default);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function forget(string $name): bool
    {
        return delete_option($name);
    }

}