<?php

namespace Twist\Library\Model;

/**
 * Class Enumerator
 *
 * @package Twist\Library\Model
 */
class Enumerator implements EnumeratorInterface
{

	/**
	 * @var ModelInterface
	 */
	protected $model;

	/**
	 * @var array
	 */
	protected $values;

	/**
	 * Enumerator constructor.
	 *
	 * @param ModelInterface $model
	 * @param array          $values
	 */
	public function __construct(ModelInterface $model, array $values = [])
	{
		$this->model  = $model;
		$this->values = $values;
	}

	/**
	 * @inheritdoc
	 */
	public function model(): ModelInterface
	{
		return $this->model;
	}

	/**
	 * @inheritdoc
	 */
	public function set($id, $value): void
	{
		$this->values[$id] = $value;
	}

	/**
	 * @inheritdoc
	 */
	public function get($id)
	{
		return $this->values[$id] ?? null;
	}

	/**
	 * @inheritdoc
	 */
	public function has($id): bool
	{
		return array_key_exists($id, $this->values);
	}

	/**
	 * @inheritdoc
	 */
	public function unset($id): void
	{
		unset($this->values[$id]);
	}

	/**
	 * @inheritdoc
	 */
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