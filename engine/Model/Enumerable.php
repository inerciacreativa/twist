<?php

namespace Twist\Model;

use ArrayIterator;
use Twist\Library\Support\Data;

/**
 * Class Enumerator
 *
 * @package Twist\Model
 */
class Enumerable implements EnumerableInterface
{

	/**
	 * @var array
	 */
	private $values = [];

	/**
	 * @inheritDoc
	 */
	public function count(): int
	{
		return count($this->values);
	}

	/**
	 * @param array $values
	 */
	protected function fill(array $values): void
	{
		$this->values = $values;
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 */
	protected function set(string $name, $value): void
	{
		$this->values[$name] = $value;
	}

	/**
	 * @inheritDoc
	 *
	 * @param mixed $default
	 */
	public function get(string $name, $default = null)
	{
		return $this->values[$name] ?? Data::value($default);
	}

	/**
	 * @inheritDoc
	 */
	public function has(string $name): bool
	{
		return array_key_exists($name, $this->values);
	}

	/**
	 * @inheritDoc
	 */
	public function getValues(): array
	{
		return $this->values;
	}

	/**
	 * @inheritDoc
	 */
	public function getNames(): array
	{
		return array_keys($this->values);
	}

	/**
	 * @inheritDoc
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->getValues());
	}

}
