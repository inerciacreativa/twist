<?php

namespace Twist\Library\Model;

trait Enumerator
{

	/**
	 * @var array
	 */
	protected $values = [];

	public function get($id)
	{
		return $this->values[$id] ?? null;
	}

	public function exists($id): bool
	{
		return array_key_exists($id, $this->values);
	}

	public function add($id, $value): void
	{
		$this->values[$id] = $value;
	}

	public function remove($id): void
	{
		unset($this->values[$id]);
	}

	public function count(): int
	{
		return \count($this->values);
	}

	/**
	 * @inheritdoc
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->values);
	}

}