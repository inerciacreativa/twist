<?php

namespace Twist\Library\Support;

use BadMethodCallException;
use Closure;

/**
 * Trait Macroable
 *
 * @package Twist\Library\Support
 */
trait Macroable
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
	public static function macro(string $name, callable $macro): void
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
	public static function hasMacro(string $name): bool
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
	 * @throws BadMethodCallException
	 */
	public static function __callStatic(string $method, array $parameters)
	{
		if (!static::hasMacro($method)) {
			throw new BadMethodCallException("Method {$method} does not exist.");
		}

		if (static::$macros[$method] instanceof Closure) {
			return call_user_func_array(Closure::bind(static::$macros[$method], null, static::class), $parameters);
		}

		return call_user_func_array(static::$macros[$method], $parameters);
	}

	/**
	 * Dynamically handle calls to the class.
	 *
	 * @param string $method
	 * @param array  $parameters
	 *
	 * @return mixed
	 *
	 * @throws BadMethodCallException
	 */
	public function __call(string $method, array $parameters)
	{
		if (!static::hasMacro($method)) {
			throw new BadMethodCallException("Method {$method} does not exist.");
		}

		$callable = &static::$macros[$method];

		if ($callable instanceof Closure) {
			return call_user_func_array($callable->bindTo($this, get_class($this)), $parameters);
		}

		return call_user_func_array($callable, $parameters);
	}

}