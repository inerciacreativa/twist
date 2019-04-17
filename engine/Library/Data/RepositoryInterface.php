<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */

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
	 * @param string|array $key
	 *
	 * @return bool
	 */
	public function has($key): bool;

	/**
	 * @param string|array|object $key
	 * @param mixed               $value
	 *
	 * @return $this
	 */
	public function add($key, $value = null);

	/**
	 * @param string|array|object $key
	 * @param mixed               $value
	 *
	 * @return $this
	 */
	public function set($key, $value = null);

	/**
	 * @param string|array $key
	 *
	 * @return $this
	 */
	public function forget($key);

	/**
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function pull(string $key, $default = null);

	/**
	 * Overwrites the values that already exists in the repository using "dot" notation.
	 *
	 * @param array|mixed $values
	 *
	 * @return $this
	 */
	public function fill($values);

	/**
	 * @param array $values
	 *
	 * @return $this
	 */
	public function merge(array $values);

}