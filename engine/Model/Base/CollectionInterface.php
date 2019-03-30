<?php

namespace Twist\Model\Base;

use Countable;
use IteratorAggregate;

/**
 * Interface CollectionInterface
 *
 * @package Twist\Model\Base
 */
interface CollectionInterface extends Countable, HasParentInterface, IteratorAggregate
{

	/**
	 * Add a Model to the collection.
	 *
	 * @param ModelInterface $model
	 */
	public function add(ModelInterface $model): void;

	/**
	 * Get a Model from the collection.
	 *
	 * @param int $id
	 *
	 * @return null|ModelInterface
	 */
	public function get(int $id): ?ModelInterface;

	/**
	 * Remove a Model from the collection.
	 *
	 * @param int $id
	 */
	public function forget(int $id): void;

	/**
	 * Check if a Model exists in the collection.
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function has(int $id): bool;

	/**
	 * Return the number of Models in the collection.
	 *
	 * @return int
	 */
	public function count(): int;

	/**
	 * Return an array with the IDs of the Models in the collection.
	 *
	 * @return int[]
	 */
	public function ids(): array;

	/**
	 * Return an array with the Models in the collection.
	 *
	 * @return ModelInterface[]
	 */
	public function all(): array;

	/**
	 * Return the first Model in the collection passing a given truth test.
	 *
	 * @param callable|null $callback
	 * @param mixed         $default
	 *
	 * @return null|ModelInterface
	 */
	public function first(callable $callback = null, $default = null): ?ModelInterface;

	/**
	 * Return the last Model in the collection passing a given truth test.
	 *
	 * @param callable|null $callback
	 * @param mixed         $default
	 *
	 * @return null|ModelInterface
	 */
	public function last(callable $callback = null, $default = null): ?ModelInterface;

	/**
	 * Merge the collection with the given Models.
	 *
	 * @param CollectionInterface|array $models
	 *
	 * @return CollectionInterface
	 */
	public function merge($models): CollectionInterface;

	/**
	 * Get the Models with the specified IDs.
	 *
	 * @param int[] $ids
	 *
	 * @return CollectionInterface
	 */
	public function only(array $ids): CollectionInterface;

	/**
	 * Get all Models except for those with the specified IDs.
	 *
	 * @param int[] $ids
	 *
	 * @return CollectionInterface
	 */
	public function except(array $ids): CollectionInterface;

	/**
	 * Slice the underlying collection array.
	 *
	 * @param int      $offset
	 * @param int|null $length
	 *
	 * @return CollectionInterface
	 */
	public function slice(int $offset, int $length = null): CollectionInterface;

	/**
	 * Take the first or last {$limit} Models.
	 *
	 * @param int $limit
	 *
	 * @return CollectionInterface
	 */
	public function take(int $limit): CollectionInterface;

	/**
	 * Run a filter over each of the Models.
	 *
	 * @param callable $callback
	 *
	 * @return CollectionInterface
	 */
	public function filter(callable $callback): CollectionInterface;

	/**
	 * Filter Models by the given method value pair.
	 *
	 * @param string $method
	 * @param string $operator
	 * @param mixed  $value
	 *
	 * @return CollectionInterface
	 */
	public function where(string $method, string $operator, $value = null): CollectionInterface;

	/**
	 * Sort the collection using the value of the method from the Models.
	 *
	 * @param string $method
	 * @param bool   $descending
	 * @param int    $options
	 *
	 * @return CollectionInterface
	 *
	 * @see sort()
	 */
	public function sort(string $method = null, bool $descending = false, int $options = SORT_REGULAR): CollectionInterface;

}