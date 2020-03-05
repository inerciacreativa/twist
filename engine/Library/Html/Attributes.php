<?php

namespace Twist\Library\Html;

use ArrayAccess;
use Twist\Library\Support\Arr;

/**
 * Class Attributes
 *
 * @package Twist\Library\Html
 */
class Attributes implements ArrayAccess
{

	/**
	 * @var array
	 */
	protected static $urlAttributes = [
		'action',
		'cite',
		'data',
		'formaction',
		'href',
		'src',
	];

	/**
	 * @var array
	 */
	protected static $boolAttributes = [
		'async',
		'autofocus',
		'capture',
		'checked',
		'controls',
		'crossorigin',
		'default',
		'defer',
		'disabled',
		'formnovalidate',
		'hidden',
		'ismap',
		'itemscope',
		'loop',
		'multiple',
		'muted',
		'novalidate',
		'open',
		'readonly',
		'required',
		'reversed',
		'selected',
	];

	/**
	 * @var array
	 */
	protected static $emptyAttributes = [
		'value',
		'alt',
	];

	/**
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * @var Classes
	 */
	protected $classes;

	/**
	 * @var Tag
	 */
	protected $tag;

	/**
	 * Attributes constructor.
	 *
	 * @param array $attributes
	 * @param Tag   $tag
	 */
	public function __construct(array $attributes = [], Tag $tag = null)
	{
		$this->classes = $this->getClassesInstance();
		$this->tag     = $tag;

		$this->add($attributes);
	}

	/**
	 * @return Tag|null
	 */
	public function tag(): ?Tag
	{
		return $this->tag;
	}

	/**
	 * @param array $attributes
	 *
	 * @return $this
	 */
	public function add(array $attributes): self
	{
		foreach ($attributes as $name => $value) {
			$this->set($name, $value);
		}

		return $this;
	}

	/**
	 * @param string     $name
	 * @param mixed|null $value
	 *
	 * @return $this
	 */
	public function set(string $name, $value): self
	{
		if ($value === null) {
			return $this->unset($name);
		}

		$name  = strtolower($name);
		$value = $this->getValidValue($name, $value);

		if ($value !== null) {
			if ($name === 'class') {
				$this->classes->add($value);
			} else {
				$this->attributes[$name] = $value;
			}
		}

		return $this;
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function unset(string $name): self
	{
		$name = strtolower($name);

		if ($name === 'class') {
			$this->classes = $this->getClassesInstance();
		} else {
			unset($this->attributes[$name]);
		}

		return $this;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function has(string $name): bool
	{
		$name = strtolower($name);

		if ($name === 'class') {
			return $this->classes->count() > 0;
		}

		return array_key_exists($name, $this->attributes);
	}

	/**
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	public function get(string $name)
	{
		$name = strtolower($name);

		if (!$this->has($name)) {
			return null;
		}

		if ($name === 'class') {
			return $this->classes->get();
		}

		return $this->attributes[$name];
	}

	/**
	 * @return Classes
	 */
	public function classes(): Classes
	{
		return $this->classes;
	}

	/**
	 * Test whether the attribute exists.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function offsetExists($name): bool
	{
		return $this->has($name);
	}

	/**
	 * Get an attribute.
	 *
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	public function offsetGet($name)
	{
		return $this->get($name);
	}

	/**
	 * Set the value of a given attribute.
	 *
	 * @param string     $name
	 * @param mixed|null $value
	 *
	 * @return void
	 *
	 */
	public function offsetSet($name, $value): void
	{
		$this->set($name, $value);
	}

	/**
	 * Unset the attribute.
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	public function offsetUnset($name): void
	{
		$this->unset($name);
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		$attributes = array_merge($this->attributes, ['class' => $this->classes->render()]);

		$attributes = Arr::map($attributes, static function ($value, $name) {
			if (static::isBool($name)) {
				return $value ? $name : '';
			}

			if (($value === '') && !static::canBeEmpty($name)) {
				return '';
			}

			if ($name !== 'class') {
				$value = static::isUrl($name) ? esc_url($value) : esc_attr($value);
			}

			return sprintf('%s="%s"', $name, $value);
		});

		return trim(implode(' ', array_filter($attributes)));
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * @param string $attribute
	 *
	 * @return bool
	 */
	public static function isBool(string $attribute): bool
	{
		return in_array($attribute, static::$boolAttributes, true);
	}

	/**
	 * @param string $attribute
	 *
	 * @return bool
	 */
	public static function isUrl(string $attribute): bool
	{
		return in_array($attribute, static::$urlAttributes, true);
	}

	/**
	 * @param string $attribute
	 *
	 * @return bool
	 */
	public static function canBeEmpty(string $attribute): bool
	{
		return in_array($attribute, static::$emptyAttributes, true);
	}

	/**
	 * @return Classes
	 */
	protected function getClassesInstance(): Classes
	{
		return new Classes([], $this);
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return mixed|null
	 */
	protected function getValidValue(string $name, $value)
	{
		if (is_string($value) || is_bool($value) || is_int($value) || is_float($value)) {
			return $value;
		}

		if ($name === 'class' && (is_array($value) || $value instanceof Classes)) {
			return $value;
		}

		return null;
	}

}
