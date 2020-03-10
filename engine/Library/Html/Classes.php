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
	 * @param array|string $classes
	 *
	 * @return $this
	 */
	public function remove($classes): self
	{
		foreach (self::parse($classes) as $class) {
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
	public function get(): array
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
	 * @param array|string $classes
	 *
	 * @return $this
	 */
	public function only($classes): self
	{
		$this->classes = array_intersect($this->classes, self::parse($classes));

		return $this;
	}

	/**
	 * @param array|string $search
	 * @param array|string $replace
	 *
	 * @return $this
	 */
	public function replace($search, $replace): self
	{
		$this->classes = self::parse(str_replace(self::parse($search), self::parse($replace), $this->render()));

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
		return implode(' ', $this->get());
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
	 * @param array|string $classes
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
	 * @see sanitize_html_class()
	 *
	 */
	public static function sanitize(string $class, string $fallback = ''): string
	{
		$sanitized = preg_replace('/%[a-f0-9]{2}/i', '', $class);
		$sanitized = preg_replace('/[^a-z0-9_-]/i', '', $sanitized);

		$valid = preg_match('/^-?[_a-z]+[_a-z0-9-]*/im', $sanitized);

		if ($fallback && ($sanitized === '' || !$valid)) {
			return self::sanitize($fallback);
		}

		return $valid ? $sanitized : '';
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
			return $value->get();
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
		$values = array_map([
			self::class,
			'parseString',
		], Arr::flatten($values));

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
