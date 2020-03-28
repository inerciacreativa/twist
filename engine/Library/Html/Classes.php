<?php

namespace Twist\Library\Html;

use ArrayAccess;
use Countable;
use Twist\Library\Support\Arr;

/**
 * Class Classes
 *
 * @package Twist\Library\Html
 */
class Classes implements ArrayAccess, Countable
{

	/**
	 * @var array
	 */
	protected $classes = [];

	/**
	 * @var Attributes
	 */
	protected $attributes;

	/**
	 * @param array|string $classes
	 *
	 * @return static
	 */
	public static function make($classes = []): self
	{
		return new static($classes);
	}

	/**
	 * Classes constructor.
	 *
	 * @param array|string $classes
	 * @param Attributes   $attributes
	 */
	public function __construct($classes = [], Attributes $attributes = null)
	{
		$this->set($classes);
		$this->attributes = $attributes;
	}

	/**
	 * @return Attributes|null
	 */
	public function attributes(): ?Attributes
	{
		return $this->attributes;
	}

	/**
	 * @return Tag|null
	 */
	public function tag(): ?Tag
	{
		if ($this->attributes && $tag = $this->attributes->tag()) {
			return $tag;
		}

		return null;
	}

	/**
	 * @param array|string $classes
	 *
	 * @return $this
	 */
	public function set($classes): self
	{
		$this->classes = self::parse($classes);

		return $this;
	}

	/**
	 * @param array|string $classes
	 *
	 * @return $this
	 */
	public function add($classes): self
	{
		$this->classes = self::parse([$this->classes, $classes]);

		return $this;
	}

	/**
	 * @param array $classes
	 *
	 * @return $this
	 */
	public function remove(array $classes): self
	{
		foreach ($classes as $class) {
			if (($index = array_search($class, $this->classes, true)) !== false) {
				unset($this->classes[$index]);
			}
		}

		$this->classes = array_values($this->classes);

		return $this;
	}

	/**
	 * @return array
	 */
	public function all(): array
	{
		return $this->classes;
	}

	/**
	 * @param string $class
	 *
	 * @return bool
	 */
	public function has(string $class): bool
	{
		return in_array($class, $this->classes, true);
	}

	/**
	 * @param array $classes
	 *
	 * @return $this
	 */
	public function only(array $classes): self
	{
		$this->classes = array_intersect($this->classes, $classes);

		return $this;
	}

	/**
	 * @param string|array $search
	 * @param string|array $replace
	 *
	 * @return $this
	 */
	public function replace($search, $replace): self
	{
		$this->classes = self::parse(str_replace($search, $replace, $this->render()));

		return $this;
	}

	/**
	 * @return int
	 */
	public function count(): int
	{
		return count($this->classes);
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		return implode(' ', $this->all());
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->render();
	}

	/**
	 * @param string $class
	 *
	 * @return bool
	 */
	public function offsetExists($class): bool
	{
		return $this->has($class);
	}

	/**
	 * @param mixed $index
	 */
	public function offsetGet($index): void
	{
	}

	/**
	 * @param mixed        $index
	 * @param array|string $classes
	 */
	public function offsetSet($index, $classes): void
	{
		$this->add($classes);
	}

	/**
	 * @param array $classes
	 */
	public function offsetUnset($classes): void
	{
		$this->remove($classes);
	}

	/**
	 * @param string $class
	 * @param string $fallback
	 *
	 * @return string
	 *
	 * @see sanitize_html_class()
	 */
	public static function sanitize(string $class, string $fallback = ''): string
	{
		// Strip out any % encoded octets
		$sanitized = preg_replace('/%[a-f0-9]{2}/i', '', $class);
		// Limit to A-Z,a-z,0-9,_,-
		$sanitized = preg_replace('/[^a-z0-9_-]/i', '', $sanitized);

		$valid = self::isValid($sanitized);

		if ($fallback && !$valid) {
			return self::sanitize($fallback);
		}

		return $valid ? $sanitized : '';
	}

	/**
	 * @param string $class
	 *
	 * @return bool
	 */
	protected static function isValid(string $class): bool
	{
		return !($class === '' || preg_match('/^-?[_a-z]+[_a-z0-9-]*/im', $class) !== 1);
	}

	/**
	 * @param array|string $value
	 *
	 * @return array
	 */
	public static function parse($value): array
	{
		if (empty($value)) {
			return [];
		}

		if (is_string($value)) {
			return self::filter(self::parseString($value));
		}

		if (is_array($value)) {
			return self::filter(self::parseArray($value));
		}

		if ($value instanceof self) {
			return $value->all();
		}

		return [];
	}

	/**
	 * @param string $value
	 *
	 * @return array
	 */
	protected static function parseString(string $value): array
	{
		return (array) preg_split('#\s+#', trim($value));
	}

	/**
	 * @param array $values
	 *
	 * @return array
	 */
	protected static function parseArray(array $values): array
	{
		$values = Arr::flatten($values);
		$values = array_filter($values);
		$values = array_map([self::class, 'parseString'], $values);

		return array_merge(...$values);
	}

	/**
	 * @param array $values
	 *
	 * @return array
	 */
	protected static function filter(array $values): array
	{
		$values = array_map([self::class, 'sanitize'], $values);
		$values = array_unique($values);

		return array_values(array_filter($values));
	}

}
