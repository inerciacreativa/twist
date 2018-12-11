<?php

namespace Twist\Library\Html;

/**
 * Class Classes
 *
 * @package Twist\Library\Html
 */
class Classes implements \ArrayAccess
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
		$this->classes = $this->parse($classes);

		return $this;
	}

	/**
	 * @param array|string $classes
	 *
	 * @return $this
	 */
	public function add($classes): self
	{
		$this->classes = array_unique(array_merge($this->classes, $this->parse($classes)));

		return $this;
	}

	/**
	 * @param array|string $classes
	 *
	 * @return $this
	 */
	public function remove($classes): self
	{
		foreach ($this->parse($classes) as $class) {
			if (($index = array_search($class, $this->classes, true)) !== false) {
				unset($this->classes[$index]);
			}
		}

		return $this;
	}

	/**
	 * @param array|string $classes
	 *
	 * @return $this
	 */
	public function only($classes): self
	{
		$this->classes = array_intersect($this->classes, $this->parse($classes));

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
		$this->classes = $this->parse(str_replace($this->parse($search), $this->parse($replace), $this->render()));

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
	 * @see sanitize_html_class()
	 *
	 * @param string $class
	 * @param string $fallback
	 *
	 * @return string
	 */
	public function sanitize(string $class, string $fallback = ''): string
	{
		$sanitized = preg_replace('/%[a-f0-9]{2}/i', '', $class);
		$sanitized = preg_replace('/[^a-z0-9_-]/i', '', $sanitized);

		if ($sanitized === '' && $fallback) {
			return $this->sanitize($fallback);
		}

		return apply_filters('sanitize_html_class', $sanitized, $class, $fallback);
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
	 * @param array|string $value
	 *
	 * @return array
	 */
	protected function parse($value): array
	{
		if (empty($value)) {
			return [];
		}

		if (is_string($value)) {
			return (array) preg_split('#\s+#', $value);
		}

		if (is_array($value)) {
			return $value;
		}

		return [];
	}

}