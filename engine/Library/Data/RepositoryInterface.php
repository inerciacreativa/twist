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
	 * Get all items.
	 *
	 * @return array
	 */
	public function all(): array;

	/**
	 * Get an item using "dot" notation.
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get(string $key, $default = null);

	/**
	 * Check if an item or items exists using "dot" notation.
	 *
	 * @param string|array $key
	 *
	 * @return bool
	 */
	public function has($key): bool;

	/**
	 * Add an element using "dot" notation if it doesn't exist.
	 *
	 * @param string|array|object $key
	 * @param mixed               $value
	 *
	 * @return $this
	 */
	public function add($key, $value = null);

	/**
	 * Set an item to a given value using "dot" notation.
	 *
	 * @param string|array|object $key
	 * @param mixed               $value
	 *
	 * @return $this
	 */
	public function set($key, $value = null);

	/**
	 * Delete one or more items using "dot" notation.
	 *
	 * @param string|array $keys
	 *
	 * @return $this
	 */
	public function forget($keys);

	/**
	 * Get and delete an item using "dot" notation.
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function fetch(string $key, $default = null);

	/**
	 * Overwrites the values that already exists in the repository using "dot" notation.
	 *
	 * @param array|mixed $values
	 *
	 * @return $this
	 */
	public function fill($values);

	/**
	 * Merge the items with the items from an array.
	 *
	 * @param array $values
	 *
	 * @return $this
	 */
	public function merge(array $values);

}
