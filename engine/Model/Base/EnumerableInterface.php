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
	public function forget(string $key): bool;

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has(string $key): bool;

}