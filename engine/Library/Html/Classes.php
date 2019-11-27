<?php

namespace Twist\Library\Html;

use ArrayAccess;
use Twist\Library\Support\Arr;

/**
 * Class Classes
 *
 * @package Twist\Library\Html
 */
class Classes implements ArrayAccess
{

	/**
	 * @var array
	 */
	private $classes = [];

	/**
	 * @var string
	 */
	private $prefix = '';

	/**
	 * @var string
	 */
	private $separator = '-';

	/**
	 * @param array|string $classes
	 *
	 * @return Classes
	 */
	public static function make($classes = []): Classes
	{
		return new static($classes);
	}

	/**
	 * Classes constructor.
	 *
	 * @param array|string $classes
	 */
	public function __construct($classes = [])
	{
		if (!empty($classes)) {
			$this->set($classes);
		}
	}

	/**
	 * @param string $prefix
	 * @param string $separator
	 *
	 * @return $this
	 */
	public function prefix(string $prefix = '', string $separator = '-'): self
	{
		$this->prefix    = $prefix;
		$this->separator = $separator;

		return $this;
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
	 * @return array
	 */
	public function all(): array
	{
		$classes = $this->classes;

		if (!empty($this->prefix)) {
			$classes = array_map(function (string $class) {
				if ($this->prefix === $class) {
					return $class;
				}

				return $this->prefix . $this->separator . $class;
			}, $classes);
		}

		return $classes;
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

		if ($sanitized === '' && $fallback) {
			return self::sanitize($fallback);
		}

		return $sanitized;
	}

	/**
	 * @param array|string $value
	 *
	 * @return array
	 */
	protected static function parse($value): array
	{
		if (empty($value)) {
			return [];
		}

		if (is_string($value)) {
			return self::filter((array) preg_split('#\s+#', trim($value)));
		}

		if (is_array($value)) {
			return self::filter(array_map([self::class, 'parse'], $value), true);
		}

		return [];
	}

	/**
	 * @param array $values
	 * @param bool  $array
	 *
	 * @return array
	 */
	protected static function filter(array $values, bool $array = false): array
	{
		if ($array) {
			$values = Arr::flatten($values);
		}

		$values = array_map([self::class, 'sanitize'], $values);
		$values = array_filter($values);

		return array_unique($values);
	}

}
