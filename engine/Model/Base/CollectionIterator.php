<?php

namespace Twist\Model\Base;

use OutOfBoundsException;

/**
 * Class CollectionIterator
 *
 * @package Twist\Model\Base
 */
class CollectionIterator implements CollectionIteratorInterface
{

	/**
	 * @var ModelInterface[]
	 */
	protected $models;

	/**
	 * CollectionIterator constructor.
	 *
	 * @param array $models
	 */
	public function __construct(array $models)
	{
		$this->models = $models;

		reset($this->models);
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
	public function seek($position): void
	{
		if (func_num_args() !== 1) {
			return;
		}

		if ($position < 0 || $position >= count($this->models)) {
			throw new OutOfBoundsException("Seek position {$position} is out of range");
		}

		reset($this->models);
		for ($i = 0; $i < $position; $i++) {
			if (!next($this->models)) {
				break;
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function rewind(): void
	{
		reset($this->models);
	}

	/**
	 * @inheritdoc
	 */
	public function current(): ?ModelInterface
	{
		return (key($this->models) === null) ? null : current($this->models);
	}

	/**
	 * @inheritdoc
	 */
	public function key(): ?int
	{
		return key($this->models);
	}

	/**
	 * @inheritdoc
	 */
	public function next(): void
	{
		next($this->models);
	}

	/**
	 * @inheritdoc
	 */
	public function valid(): bool
	{
		return key($this->models) !== null;
	}

	/**
	 * @inheritdoc
	 */
	public function offsetExists($id): bool
	{
		return isset($this->models[$id]);
	}

	/**
	 * @inheritdoc
	 */
	public function offsetGet($id): ?ModelInterface
	{
		return $this->models[$id] ?? null;
	}

	/**
	 * @inheritdoc
	 */
	public function offsetSet($id, $model): void
	{
	}

	/**
	 * @inheritdoc
	 */
	public function offsetUnset($id): void
	{
	}

}