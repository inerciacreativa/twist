<?php

namespace Twist\Library\Support;

use Closure;
use InvalidArgumentException;
use RuntimeException;
use Twist\Library\Data\Collection;

/**
 * Class Data
 *
 * @package Twist\Library\Support
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

		if (is_object($target) && isset($target->{$key})) {
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
		return (bool) static::fetch($target, $key);
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

		$key = is_array($key) ? $key : explode('.', $key);

		while (($segment = array_shift($key)) !== null) {
			if ($segment === '*') {
				return ($target instanceof Collection || is_array($target));
			}

			if (Arr::accessible($target) && Arr::exists($target, $segment)) {
				$target = $target[$segment];
			} else if (is_object($target) && isset($target->{$segment})) {
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

		$key = is_array($key) ? $key : explode('.', $key);

		while (($segment = array_shift($key)) !== null) {
			if ($segment === '*') {
				if ($target instanceof Collection) {
					$target = $target->all();
				} else if (!is_array($target)) {
					return static::value($default);
				}

				$result = Arr::pluck($target, $key);

				return in_array('*', $key, false) ? Arr::collapse($result) : $result;
			}

			if (Arr::accessible($target) && Arr::exists($target, $segment)) {
				$target = $target[$segment];
			} else if (is_object($target) && isset($target->{$segment})) {
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
		$segments = is_array($key) ? $key : explode('.', $key);

		if (($segment = array_shift($segments)) === '*') {
			if (!Arr::accessible($target)) {
				$target = [];
			}

			if ($segments) {
				foreach ($target as &$inner) {
					static::set($inner, $segments, $value, $overwrite);
				}
			} else if ($overwrite) {
				foreach ($target as &$inner) {
					$inner = $value;
				}
			}

			unset($inner);
		} else if (Arr::accessible($target)) {
			if ($segments) {
				if (!Arr::exists($target, $segment)) {
					$target[$segment] = [];
				}

				static::set($target[$segment], $segments, $value, $overwrite);
			} else if ($overwrite || !Arr::exists($target, $segment)) {
				$target[$segment] = $value;
			}
		} else if (is_object($target)) {
			if ($segments) {
				if (!isset($target->{$segment})) {
					$target->{$segment} = [];
				}

				static::set($target->{$segment}, $segments, $value, $overwrite);
			} else if ($overwrite || !isset($target->{$segment})) {
				$target->{$segment} = $value;
			}
		} else {
			$target = [];

			if ($segments) {
				static::set($target[$segment], $segments, $value, $overwrite);
			} else if ($overwrite) {
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
		if ($value instanceof Closure) {
			return true;
		}

		if (is_object($value) && method_exists($value, '__invoke')) {
			return true;
		}

		return false;
	}

	/**
	 * @param string $data
	 * @param bool   $strict
	 *
	 * @return bool
	 */
	public static function isSerialized($data, bool $strict = true): bool
	{
		if (!is_string($data)) {
			return false;
		}

		$data = trim($data);
		if ('N;' === $data) {
			return true;
		}

		if (strlen($data) < 4) {
			return false;
		}

		if (':' !== $data[1]) {
			return false;
		}

		if ($strict) {
			$last = substr($data, -1);
			if (';' !== $last && '}' !== $last) {
				return false;
			}
		} else {
			$semicolon = strpos($data, ';');
			$brace     = strpos($data, '}');

			// Either ; or } must exist.
			if (false === $semicolon && false === $brace) {
				return false;
			}

			// But neither must be in the first X characters.
			if (false !== $semicolon && $semicolon < 3) {
				return false;
			}

			if (false !== $brace && $brace < 4) {
				return false;
			}
		}

		$token = $data[0];
		switch ($token) {
			case 's':
				if ($strict) {
					if ('"' !== substr($data, -2, 1)) {
						return false;
					}
				} else if (false === strpos($data, '"')) {
					return false;
				}
			// or else fall through
			case 'a':
			case 'O':
				return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
			case 'b':
			case 'i':
			case 'd':
				$end = $strict ? '$' : '';

				return (bool) preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
		}

		return false;
	}

	/**
	 * @param mixed $data
	 * @param bool  $compatible
	 *
	 * @return string
	 */
	public static function serialize($data, bool $compatible = false): string
	{
		if (is_array($data) || is_object($data)) {
			return serialize($data);
		}

		// Double serialization is required for WP backward compatibility.
		$serialized = self::isSerialized($data, false);
		if (($compatible && $serialized) || (!$compatible && !$serialized)) {
			return serialize($data);
		}

		return $data;

	}

	/**
	 * @param string $data
	 * @param bool   $options
	 *
	 * @return mixed
	 */
	public static function unserialize(string $data, $options = false)
	{
		if (self::isSerialized($data)) {
			$exception = null;
			set_error_handler(static function () use (&$exception) {
				$exception = new RuntimeException('Unable to unserialize data.');
			});
			$data = @unserialize($data, $options);
			restore_error_handler();

			if ($exception) {
				/** @var $exception RuntimeException */
				throw $exception;
			}
		}

		return $data;
	}

	/**
	 * @param mixed $value
	 * @param int   $options
	 * @param int   $depth
	 *
	 * @return string
	 */
	public static function encode($value, int $options = 0, int $depth = 512): string
	{
		$json = json_encode($value, $options, $depth);
		if (JSON_ERROR_NONE !== json_last_error()) {
			throw new InvalidArgumentException('json_encode error: ' . json_last_error_msg());
		}

		return $json;
	}

	/**
	 * @param string $json
	 * @param bool   $assoc
	 * @param int    $depth
	 * @param int    $options
	 *
	 * @return array|object
	 */
	public static function decode(string $json, bool $assoc = false, int $depth = 512, int $options = 0)
	{
		$data = json_decode($json, $assoc, $depth, $options);
		if (JSON_ERROR_NONE !== json_last_error()) {
			throw new InvalidArgumentException('json_decode error: ' . json_last_error_msg());
		}

		return $data;
	}

}
