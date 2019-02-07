<?php

namespace Twist\Model\Base;

/**
 * Interface EnumeratorInterface
 *
 * @package Twist\Model\Base
 */
interface EnumerableInterface extends \IteratorAggregate
{

	/**
	 * Set a key value pair in the set.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return EnumerableInterface
	 */
	public function set(string $key, $value): EnumerableInterface;

	/**
	 * Get a value from the set.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get(string $key);

	/**
	 * Remove a value from the set.
	 *
	 * @param string $key
	 *
	 * @return EnumerableInterface
	 */
	public function forget(string $key): EnumerableInterface;

	/**
	 * Check if a key exists in the set.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has(string $key): bool;

	/**
	 * Return the set.
	 *
	 * @return array
	 */
	public function all(): array;

}