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
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get(string $name);

	/**
	 * Check if a key exists in the set.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function has(string $name): bool;

	/**
	 * Return all the values.
	 *
	 * @return array
	 */
	public function getValues(): array;

	/**
	 * Return all the names.
	 *
	 * @return array
	 */
	public function getNames(): array;

}
