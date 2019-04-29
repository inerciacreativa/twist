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
	 * @param string $key
	 * @param mixed  $value
	 * @param int    $expiration
	 *
	 * @return bool
	 */
	public static function set(string $key, $value, int $expiration = 0): bool
	{
		return set_transient($key, $value, $expiration);
	}

	/**
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public static function get(string $key, $default = false)
	{
		$value = get_transient($key);

		return ($value === false) ? $default : $value;
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public static function forget(string $key): bool
	{
		return delete_transient($key);
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public static function has(string $key): bool
	{
		return self::get($key) !== false;
	}

}