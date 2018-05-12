<?php

namespace Twist\Library\Model;

/**
 * Interface CollectionInterface
 *
 * @package Twist\Library\Model
 */
interface CollectionInterface extends HasParentInterface, \IteratorAggregate, \Countable
{

	/**
	 * @param ModelInterface $model
	 */
	public function add(ModelInterface $model): void;

	/**
	 * @param int $id
	 *
	 * @return null|ModelInterface
	 */
	public function get(int $id): ?ModelInterface;

	/**
	 * @param int $id
	 */
	public function remove(int $id): void;

	/**
	 * @param int $id
	 *
	 * @return bool
	 */
	public function has(int $id): bool;

	/**
	 * @return int[]
	 */
	public function ids(): array;

	/**
	 * @param callable|null $callback
	 *
	 * @return null|ModelInterface
	 */
	public function first(callable $callback = null): ?ModelInterface;

	/**
	 * @param callable|null $callback
	 *
	 * @return null|ModelInterface
	 */
	public function last(callable $callback = null): ?ModelInterface;

	/**
	 * @param int[] $ids
	 *
	 * @return CollectionInterface
	 */
	public function only(array $ids): CollectionInterface;

	/**
	 * @param int[] $ids
	 *
	 * @return CollectionInterface
	 */
	public function except(array $ids): CollectionInterface;

	/**
	 * @param int $limit
	 *
	 * @return CollectionInterface
	 */
	public function take(int $limit): CollectionInterface;

}