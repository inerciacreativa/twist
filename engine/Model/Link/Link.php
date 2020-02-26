<?php

namespace Twist\Model\Link;

use Twist\Library\Html\Classes;
use Twist\Library\Support\Arr;
use Twist\Model\Model;

/**
 * Class Link
 *
 * @package Twist\Model\Link
 *
 */
abstract class Link extends Model
{

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Link constructor.
	 *
	 * @param array $properties
	 */
	public function __construct(array $properties)
	{
		$properties = array_merge([
			'id'      => 0,
			'title'   => '',
			'href'    => null,
			'class'   => [],
		], $properties);

		$this->id    = Arr::pull($properties, 'id');
		$this->title = Arr::pull($properties, 'title');

		if (!($properties['class'] instanceof Classes)) {
			$properties['class'] = new Classes($properties['class']);
		}

		$properties['class']->only(array_keys(static::CLASSES))
			 ->replace(array_keys(static::CLASSES), static::CLASSES);

		$this->attributes = $properties;
	}

	/**
	 * @return string
	 */
	abstract public function render(): string;

	/**
	 * @inheritdoc
	 */
	public function id(): int
	{
		return $this->id;
	}

	/**
	 * @param null|string $title
	 *
	 * @return string|self
	 */
	public function title(string $title = null)
	{
		if (empty($title)) {
			return $this->title;
		}

		$this->title = $title;

		return $this;
	}

	/**
	 * @return null|string
	 */
	public function url(): ?string
	{
		return $this->attributes['href'];
	}

	/**
	 * @param null|string|array $classes
	 * @param bool $add
	 *
	 * @return Classes|self
	 */
	public function class($classes = null, bool $add = false)
	{
		if (empty($classes)) {
			return $this->attributes['class'];
		}

		if ($add) {
			$this->attributes['class']->add($classes);
		} else {
			$this->attributes['class']->set($classes);
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function is_disabled(): bool
	{
		return empty($this->attributes['href']);
	}

	/**
	 * @return bool
	 */
	public function is_current(): bool
	{
		return $this->class()->has('is-current');
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->render();
	}

}
