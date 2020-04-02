<?php

namespace Twist\Model;

use OutOfBoundsException;

/**
 * Class CollectionIterator
 *
 * @package Twist\Model
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
	 * @inheritDoc
	 */
	public function count(): int
	{
		return count($this->models);
	}

	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function rewind(): void
	{
		reset($this->models);
	}

	/**
	 * @inheritDoc
	 */
	public function current(): ?ModelInterface
	{
		return (key($this->models) === null) ? null : current($this->models);
	}

	/**
	 * @inheritDoc
	 */
	public function key(): ?int
	{
		return key($this->models);
	}

	/**
	 * @inheritDoc
	 */
	public function next(): void
	{
		next($this->models);
	}

	/**
	 * @inheritDoc
	 */
	public function valid(): bool
	{
		return key($this->models) !== null;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetExists($id): bool
	{
		return isset($this->models[$id]);
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet($id): ?ModelInterface
	{
		return $this->models[$id] ?? null;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet($id, $model): void
	{
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset($id): void
	{
	}

}
