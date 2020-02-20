<?php

namespace Twist\Model;

use IteratorAggregate;

/**
 * Interface EnumeratorInterface
 *
 * @package Twist\Model
 */
interface EnumerableInterface extends IteratorAggregate
{

	/**
	 * Get a value from the set.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get(string $key);

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
