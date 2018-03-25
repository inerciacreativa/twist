<?php

namespace Twist\Library\Util;

/**
 * Class Macro
 *
 * @package Twist\Library\Util
 */
trait Macro
{

    /**
     * The registered string macros.
     *
     * @var array
     */
    protected static $macros = [];

    /**
     * Register a custom macro.
     *
     * @param string   $name
     * @param callable $macro
     *
     * @return void
     */
    public static function macro($name, callable $macro)
    {
        static::$macros[$name] = $macro;
    }

    /**
     * Checks if macro is registered.
     *
     * @param string $name
     *
     * @return bool
     */
    public static function hasMacro($name): bool
    {
        return isset(static::$macros[$name]);
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public static function __callStatic($method, $parameters)
    {
        if (!static::hasMacro($method)) {
            throw new \BadMethodCallException("Method {$method} does not exist.");

        }

        if (static::$macros[$method] instanceof \Closure) {
            return \call_user_func_array(\Closure::bind(static::$macros[$method], null, \get_called_class()), $parameters);
        }

        return \call_user_func_array(static::$macros[$method], $parameters);
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (!static::hasMacro($method)) {
            throw new \BadMethodCallException("Method {$method} does not exist.");

        }

        if (static::$macros[$method] instanceof \Closure) {
            return \call_user_func_array(static::$macros[$method]->bindTo($this, \get_class($this)), $parameters);
        }

        return \call_user_func_array(static::$macros[$method], $parameters);
    }

}