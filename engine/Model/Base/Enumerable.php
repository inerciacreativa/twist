<?php

namespace Twist\Model\Base;

use ArrayIterator;
use Countable;
use Twist\Library\Support\Data;

/**
 * Class Enumerator
 *
 * @package Twist\Model\Base
 */
class Enumerable implements EnumerableInterface, Countable
{

	/**
	 * @var array
	 */
	private $values = [];

	/**
	 * Reset the values in the set, optionally fill with the new values passed.
	 *
	 * @param array $values
	 *
	 * @return $this
	 */
	public function reset(array $values = []): EnumerableInterface
	{
		$this->values = $values;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function set(string $key, $value): EnumerableInterface
	{
		$this->values[$key] = $value;

		return $this;
	}

	/**
	 * @inheritdoc
	 *
	 * @param mixed $default
	 */
	public function get(string $key, $default = null)
	{
		return $this->values[$key] ?? Data::value($default);
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
	public function forget(string $key): EnumerableInterface
	{
		unset($this->values[$key]);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function all(): array
	{
		return $this->values;
	}

	/**
	 * @inheritdoc
	 */
	public function count(): int
	{
		return count($this->values);
	}

	/**
	 * @inheritdoc
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->values);
	}

}