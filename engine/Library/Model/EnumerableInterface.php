<?php

namespace Twist\Library\Model;

/**
 * Interface EnumeratorInterface
 *
 * @package Twist\Library\Model
 */
interface EnumerableInterface extends \IteratorAggregate, \Countable
{

	/**
	 * @return ModelInterface
	 */
	public function model(): ModelInterface;

	/**
	 * @param $id
	 * @param $value
	 */
	public function set($id, $value): void;

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	public function get($id);

	/**
	 * @param $id
	 */
	public function unset($id): void;

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function has($id): bool;

}