<?php

namespace Twist\Model;

/**
 * Class ModelCollection
 *
 * @package Twist\Model
 */
class ModelCollection implements \Countable, \Iterator
{

	/**
	 * @var Model
	 */
	protected $parent;

	/**
	 * @var array
	 */
	protected $children = [];

	/**
	 * Collection constructor.
	 *
	 * @param Model|null $parent
	 */
	public function __construct(Model $parent = null)
	{
		$this->parent = $parent;
	}

	/**
	 * @return bool
	 */
	public function has_parent(): bool
	{
		return $this->parent !== null;
	}

	/**
	 * @return Model|null
	 */
	public function parent()
	{
		return $this->parent;
	}

	/**
	 * @param int $id
	 *
	 * @return bool
	 */
	public function has($id): bool
	{
		return array_key_exists($id, $this->children);
	}

	/**
	 * @param int $id
	 *
	 * @return Model|null
	 */
	public function get($id)
	{
		if ($this->has($id)) {
			return $this->children[$id];
		}

		return null;
	}

	/**
	 * @param Model $child
	 *
	 * @return $this
	 */
	public function add(Model $child)
	{
		$this->children[$child->id()] = $child;

		return $this;
	}

	/**
	 * @param int $id
	 *
	 * @return $this
	 */
	public function remove(int $id)
	{
		unset($this->children[$id]);

		return $this;
	}

	/**
	 * Returns the number of children.
	 *
	 * @return int
	 */
	public function count(): int
	{
		return \count($this->children);
	}

	/**
	 * Rewind to the first item.
	 */
	public function rewind()
	{
		reset($this->children);
	}

	/**
	 * Move forward to next item.
	 */
	public function next()
	{
		next($this->children);
	}

	/**
	 * Checks if current position is valid.
	 *
	 * @return bool
	 */
	public function valid(): bool
	{
		return key($this->children) !== null;
	}

	/**
	 * Returns the current item.
	 *
	 * @return Model
	 */
	public function current()
	{
		return current($this->children);
	}

	/**
	 * Returns the id of the current item.
	 *
	 * @return int
	 */
	public function key(): int
	{
		return $this->current()->id();
	}

	/**
	 * @return array
	 */
	public function ids(): array
	{
		return array_keys($this->children);
	}

	/**
	 * @return Model
	 */
	public function first()
	{
		return array_values($this->children)[0];
	}

	/**
	 * @return Model
	 */
	public function last()
	{
		return array_reverse(array_values($this->children))[0];
	}

	/**
	 * @param int $limit
	 *
	 * @return static
	 */
	public function limit(int $limit)
	{
		$collection = clone $this;

		if ($limit < 0) {
			$collection->children = \array_slice($this->children, $limit, abs($limit), true);
		} else {
			$collection->children = \array_slice($this->children, 0, $limit, true);
		}

		return $collection;
	}

	/**
	 * @param array $ids
	 *
	 * @return static
	 */
	public function filter(array $ids)
	{
		$collection = clone $this;

		$collection->children = array_filter($this->children, function ($id) use ($ids) {
			return !\in_array($id, $ids, true);
		}, ARRAY_FILTER_USE_KEY);

		return $collection;
	}

}