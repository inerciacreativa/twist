<?php

namespace Twist\Library\Model;

/**
 * Class Enumerator
 *
 * @package Twist\Library\Model
 */
class Enumerable implements EnumerableInterface
{

	/**
	 * @var IdentifiableInterface
	 */
	protected $parent;

	/**
	 * @var array
	 */
	protected $values;

	/**
	 * Enumerator constructor.
	 *
	 * @param IdentifiableInterface $parent
	 * @param array                 $values
	 */
	public function __construct(IdentifiableInterface $parent, array $values = [])
	{
		$this->parent = $parent;
		$this->values = $values;
	}

	/**
	 * @inheritdoc
	 */
	public function parent(): IdentifiableInterface
	{
		return $this->parent;
	}

	/**
	 * @inheritdoc
	 */
	public function set(string $key, $value): bool
	{
		$this->values[$key] = $value;

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function get(string $key)
	{
		return $this->values[$key] ?? null;
	}

	/**
	 * @inheritdoc
	 */
	public function has(string $key): bool
	{
		return array_key_exists($key, $this->values);
	}

	/**
	 * @inheritdoc
	 */
	public function unset(string $key): bool
	{
		unset($this->values[$key]);

		return true;
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