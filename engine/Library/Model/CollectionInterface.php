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
	 * @return ModelInterface[]
	 */
	public function all(): array;

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
	 * @param Collection $collection
	 *
	 * @return CollectionInterface
	 */
	public function merge(Collection $collection): CollectionInterface;

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
	 * @param int      $offset
	 * @param int|null $length
	 *
	 * @return CollectionInterface
	 */
	public function slice(int $offset, int $length = null): CollectionInterface;

	/**
	 * @param int $limit
	 *
	 * @return CollectionInterface
	 */
	public function take(int $limit): CollectionInterface;

	/**
	 * @param string $method
	 * @param int    $options
	 * @param bool   $descending
	 *
	 * @return CollectionInterface
	 */
	public function sort(string $method, int $options = SORT_REGULAR, bool $descending = false): CollectionInterface;

}