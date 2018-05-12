<?php

namespace Twist\Library\Model;

use Twist\Library\Util\Arr;

/**
 * Class Collection
 *
 * @package Twist\Library\Model
 */
class Collection implements CollectionInterface
{

	use HasParent;

	protected $models = [];

	/**
	 * Collection constructor.
	 *
	 * @param ModelInterface|null $parent
	 */
	public function __construct(ModelInterface $parent = null)
	{
		if ($parent) {
			$this->set_parent($parent);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function add(ModelInterface $model): void
	{
		$this->models[$model->id()] = $model;
	}

	/**
	 * @inheritdoc
	 */
	public function get(int $id): ?ModelInterface
	{
		return $this->has($id) ? $this->models[$id] : null;
	}

	/**
	 * @inheritdoc
	 */
	public function remove(int $id): void
	{
		unset($this->models[$id]);
	}

	/**
	 * @inheritdoc
	 */
	public function has(int $id): bool
	{
		return array_key_exists($id, $this->models);
	}

	/**
	 * @inheritdoc
	 */
	public function count(): int
	{
		return \count($this->models);
	}

	/**
	 * @inheritdoc
	 */
	public function ids(): array
	{
		return array_keys($this->models);
	}

	/**
	 * @inheritdoc
	 */
	public function first(callable $callback = null): ?ModelInterface
	{
		if ($callback === null) {
			return \count($this->models) > 0 ? reset($this->models) : null;
		}

		return Arr::first($this->models, $callback);
	}

	/**
	 * @inheritdoc
	 */
	public function last(callable $callback = null): ?ModelInterface
	{
		if ($callback === null) {
			return \count($this->models) > 0 ? end($this->models) : null;
		}

		return Arr::last($this->models, $callback);
	}

	/**
	 * @inheritdoc
	 */
	public function only(array $ids): CollectionInterface
	{
		$collection = clone $this;

		$collection->models = Arr::only($this->models, $ids);

		return $collection;
	}

	/**
	 * @inheritdoc
	 */
	public function except(array $ids): CollectionInterface
	{
		$collection = clone $this;

		$collection->models = Arr::except($this->models, $ids);

		return $collection;
	}

	/**
	 * @inheritdoc
	 */
	public function take(int $limit): CollectionInterface
	{
		$collection = clone $this;

		if ($limit < 0) {
			$offset = $limit;
			$length = abs($limit);
		} else {
			$offset = 0;
			$length = $limit;
		}

		$collection->models = \array_slice($this->models, $offset, $length, true);

		return $collection;
	}

	/**
	 * @inheritdoc
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->models);
	}

}