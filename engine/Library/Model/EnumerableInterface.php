<?php

namespace Twist\Library\Model;

/**
 * Interface EnumeratorInterface
 *
 * @package Twist\Library\Model
 */
interface EnumerableInterface extends \IteratorAggregate
{

	/**
	 * @return IdentifiableInterface
	 */
	public function parent(): IdentifiableInterface;

	/**
	 * @param string $key
	 * @param        $value
	 *
	 * @return bool
	 */
	public function set(string $key, $value): bool;

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get(string $key);

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function unset(string $key): bool;

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has(string $key): bool;

}