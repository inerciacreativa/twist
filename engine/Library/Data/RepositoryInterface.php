<?php

namespace Twist\Library\Data;

use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 * Interface RepositoryInterface
 *
 * @package Twist\Library\Data
 */
interface RepositoryInterface extends ArrayAccess, Countable, IteratorAggregate
{

	/**
	 * @return array
	 */
	public function all(): array;

	/**
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get(string $key, $default = null);

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has(string $key): bool;

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function add(string $key, $value);

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function set(string $key, $value);

	/**
	 * Fill the options with an array or object values.
	 *
	 * @param array|mixed $values
	 *
	 * @return $this
	 */
	public function fill($values);

	/**
	 * @param array|string $keys
	 *
	 * @return $this
	 */
	public function forget($keys);

}