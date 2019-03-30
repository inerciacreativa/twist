<?php

namespace Twist\Model\Base;

use Twist\Library\Data\Collection as DataCollection;
use Twist\Library\Util\Arr;
use Twist\Library\Util\Data;

/**
 * Class Collection
 *
 * @package Twist\Model\Base
 */
class Collection implements CollectionInterface
{

	use HasParent;

	/**
	 * @var ModelInterface[]
	 */
	protected $models = [];

	/**
	 * Collection constructor.
	 *
	 * @param ModelInterface|null $parent
	 * @param ModelInterface[]
	 */
	public function __construct(ModelInterface $parent = null, array $children = [])
	{
		if ($parent) {
			$this->set_parent($parent);
		}

		foreach ($children as $child) {
			$this->add($child);
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
	public function forget(int $id): void
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
		return count($this->models);
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
	public function all(): array
	{
		return $this->models;
	}

	/**
	 * @inheritdoc
	 */
	public function first(callable $callback = null, $default = null): ?ModelInterface
	{
		if ($callback === null) {
			return count($this->models) > 0 ? reset($this->models) : Data::value($default);
		}

		return Arr::first($this->models, $callback, $default);
	}

	/**
	 * @inheritdoc
	 */
	public function last(callable $callback = null, $default = null): ?ModelInterface
	{
		if ($callback === null) {
			return count($this->models) > 0 ? end($this->models) : Data::value($default);
		}

		return Arr::last($this->models, $callback, $default);
	}

	/**
	 * @inheritdoc
	 */
	public function merge($models): CollectionInterface
	{
		return new static($this->parent, array_merge($this->models, Arr::items($models)));
	}

	/**
	 * @inheritdoc
	 */
	public function only(array $ids): CollectionInterface
	{
		return new static($this->parent, Arr::only($this->models, $ids));
	}

	/**
	 * @inheritdoc
	 */
	public function except(array $ids): CollectionInterface
	{
		return new static($this->parent, Arr::except($this->models, $ids));
	}

	/**
	 * @inheritdoc
	 */
	public function slice(int $offset, int $length = null): CollectionInterface
	{
		return new static($this->parent, array_slice($this->models, $offset, $length, true));
	}

	/**
	 * @inheritdoc
	 */
	public function take(int $limit): CollectionInterface
	{
		if ($limit < 0) {
			return $this->slice($limit, abs($limit));
		}

		return $this->slice(0, $limit);
	}

	/**
	 * @inheritdoc
	 */
	public function filter(callable $callback): CollectionInterface
	{
		return new static($this->parent, Arr::where($this->models, $callback));
	}

	/**
	 * @inheritdoc
	 */
	public function where(string $method, string $operator, $value = null): CollectionInterface
	{
		if (func_num_args() === 2) {
			$value    = $operator;
			$operator = '=';
		}

		return $this->filter(DataCollection::operatorForWhere($method, $operator, $value));
	}

	/**
	 * @inheritdoc
	 */
	public function sort(string $method = null, bool $descending = false, int $options = SORT_REGULAR): CollectionInterface
	{
		if ($method === null) {
			return $this;
		}

		$models = [];

		foreach ($this->models as $id => $model) {
			$models[$id] = $model->$method();
		}

		$descending ? arsort($models, $options) : asort($models, $options);

		$ids = array_keys($models);
		foreach ($ids as $id) {
			$models[$id] = $this->models[$id];
		}

		return new static($this->parent, $models);
	}

	/**
	 * @inheritdoc
	 */
	public function getIterator(): CollectionIteratorInterface
	{
		return new CollectionIterator($this->models);
	}

}