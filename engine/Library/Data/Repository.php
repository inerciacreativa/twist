<?php

namespace Twist\Library\Data;

use ArrayAccess;
use ArrayIterator;
use InvalidArgumentException;
use Traversable;
use Twist\Library\Support\Arr;

/**
 * Class Repository
 *
 * @package Twist\Library\Data
 */
class Repository implements RepositoryInterface
{

	/**
	 * @var array
	 */
	protected $items = [];

	/**
	 * Repository constructor.
	 *
	 * @param array $values
	 */
	public function __construct(array $values = [])
	{
		if (!empty($values)) {
			$this->set($values);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function all(): array
	{
		return $this->items;
	}

	/**
	 * @inheritdoc
	 */
	public function get(string $key, $default = null)
	{
		return Arr::get($this->items, $key, $default);
	}

	/**
	 * @inheritdoc
	 */
	public function has($key): bool
	{
		return Arr::has($this->items, $key);
	}

	/**
	 * @inheritdoc
	 */
	public function add($key, $value = null): self
	{
		if (is_string($key)) {
			$this->items = Arr::add($this->items, $key, $value);
		} else {
			$values = self::getValues($key);
			foreach ($values as $k => $v) {
				$this->add($k, $v);
			}
		}

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function set($key, $value = null): self
	{
		if (is_string($key)) {
			Arr::set($this->items, $key, $value);
		} else {
			$values = self::getValues($key);
			foreach ($values as $k => $v) {
				$this->set($k, $v);
			}
		}

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function forget($keys): self
	{
		Arr::forget($this->items, $keys);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function fetch(string $key, $default = null)
	{
		return Arr::pull($this->items, $key, $default);
	}

	/**
	 * @inheritdoc
	 */
	public function fill($values): self
	{
		$values = self::getValues($values);
		foreach ($values as $key => $value) {
			if ($this->has($key)) {
				$this->set($key, $value);
			}
		}

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function merge(array $values): self
	{
		$values = self::getItems($values);
		Arr::merge($this->items, $values);

		return $this;
	}

	/**
	 * Determine if an item exists.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function offsetExists($key): bool
	{
		return $this->has($key);
	}

	/**
	 * Get an item.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->get($key);
	}

	/**
	 * Set the item to a given value.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function offsetSet($key, $value): void
	{
		if (empty($key)) {
			throw new InvalidArgumentException('No key was specified.');
		}

		$this->set($key ?: $this->count(), $value);
	}

	/**
	 * Unset the item.
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	public function offsetUnset($key): void
	{
		$this->forget($key);
	}

	/**
	 * Count the number of items.
	 *
	 * @return int
	 */
	public function count(): int
	{
		return count($this->items);
	}

	/**
	 * Get an iterator for the items.
	 *
	 * @return ArrayIterator
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	/**
	 * Convert the values to an array with "dot" notation.
	 *
	 * @param array|object $items
	 *
	 * @return array
	 */
	protected static function getValues($items): array
	{
		if (empty($items)) {
			return [];
		}

		return Arr::dot(self::getItems($items));
	}

	/**
	 * Convert the values to an array if possible.
	 *
	 * @param mixed $items
	 *
	 * @return array
	 */
	protected static function getItems($items): array
	{
		if (is_array($items)) {
			return $items;
		}

		if ($items instanceof self || $items instanceof Collection) {
			return $items->all();
		}

		if ($items instanceof ArrayAccess) {
			return (array) $items;
		}

		if ($items instanceof Traversable) {
			return iterator_to_array($items);
		}

		throw new InvalidArgumentException('Unable to get items: ' . print_r($items, true));
	}

}
