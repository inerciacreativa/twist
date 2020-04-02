<?php

namespace Twist\Model;

use Twist\Library\Data\Collection as DataCollection;
use Twist\Library\Support\Arr;
use Twist\Library\Support\Data;

/**
 * Class Collection
 *
 * @package Twist\Model
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
	 * @inheritDoc
	 */
	public function add(ModelInterface $model): void
	{
		if (($model instanceof HasParentInterface) && $this->has_parent()) {
			$model->set_parent($this->parent());
		}

		$this->models[$model->id()] = $model;
	}

	/**
	 * @inheritDoc
	 */
	public function get(int $id): ?ModelInterface
	{
		return $this->has($id) ? $this->models[$id] : null;
	}

	/**
	 * @inheritDoc
	 */
	public function forget(int $id): void
	{
		unset($this->models[$id]);
	}

	/**
	 * @inheritDoc
	 */
	public function has(int $id): bool
	{
		return array_key_exists($id, $this->models);
	}

	/**
	 * @inheritDoc
	 */
	public function count(): int
	{
		return count($this->models);
	}

	/**
	 * @inheritDoc
	 */
	public function ids(): array
	{
		return array_keys($this->models);
	}

	/**
	 * @inheritDoc
	 */
	public function all(): array
	{
		return $this->models;
	}

	/**
	 * @inheritDoc
	 */
	public function first(callable $callback = null, $default = null): ?ModelInterface
	{
		if ($callback === null) {
			return count($this->models) > 0 ? reset($this->models) : Data::value($default);
		}

		return Arr::first($this->models, $callback, $default);
	}

	/**
	 * @inheritDoc
	 */
	public function last(callable $callback = null, $default = null): ?ModelInterface
	{
		if ($callback === null) {
			return count($this->models) > 0 ? end($this->models) : Data::value($default);
		}

		return Arr::last($this->models, $callback, $default);
	}

	/**
	 * @inheritDoc
	 */
	public function merge($models): CollectionInterface
	{
		return new static($this->parent, array_merge($this->models, Arr::items($models)));
	}

	/**
	 * @inheritDoc
	 */
	public function only(array $ids): CollectionInterface
	{
		return new static($this->parent, Arr::only($this->models, $ids));
	}

	/**
	 * @inheritDoc
	 */
	public function except(array $ids): CollectionInterface
	{
		return new static($this->parent, Arr::except($this->models, $ids));
	}

	/**
	 * @inheritDoc
	 */
	public function slice(int $offset, int $length = null): CollectionInterface
	{
		return new static($this->parent, array_slice($this->models, $offset, $length, true));
	}

	/**
	 * @inheritDoc
	 */
	public function take(int $limit): CollectionInterface
	{
		if ($limit < 0) {
			return $this->slice($limit, abs($limit));
		}

		return $this->slice(0, $limit);
	}

	/**
	 * @inheritDoc
	 */
	public function filter(callable $callback): CollectionInterface
	{
		return new static($this->parent, Arr::where($this->models, $callback));
	}

	/**
	 * @inheritDoc
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
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function shuffle(): CollectionInterface
	{
		$models = $this->models;
		shuffle($models);

		return new static($this->parent, $models);
	}

	/**
	 * @inheritDoc
	 */
	public function getIterator(): CollectionIteratorInterface
	{
		return new CollectionIterator($this->models);
	}

}
