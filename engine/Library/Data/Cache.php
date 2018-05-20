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
	 *
	 * @return bool
	 */
	public static function set(string $name, $value, int $expiration = 0): bool
	{
		return set_transient($name, $value, $expiration);
	}

	/**
	 * @param string $name
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public static function get(string $name, $default = false)
	{
		$value = get_transient($name);

		return ($value === false) ? $default : $value;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function forget(string $name): bool
	{
		return delete_transient($name);
	}

}