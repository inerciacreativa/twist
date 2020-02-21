<?php

namespace Twist\Model;

use ArrayIterator;
use Countable;
use Twist\Library\Support\Data;

/**
 * Class Enumerator
 *
 * @package Twist\Model
 */
class Enumerable implements EnumerableInterface, Countable
{

	/**
	 * @var array
	 */
	private $values = [];

	/**
	 * @param array $values
	 */
	protected function fill(array $values): void
	{
		$this->values = $values;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	protected function set(string $key, $value): void
	{
		$this->values[$key] = $value;
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
