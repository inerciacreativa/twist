<?php

namespace Twist\Model\Link;

use Twist\Library\Html\Attributes;
use Twist\Library\Html\Classes;
use Twist\Library\Support\Arr;

/**
 * Class Link
 *
 * @package Twist\Model\Link
 */
abstract class Link implements LinkInterface
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
	 * @var bool
	 */
	protected $current;

	/**
	 * @var Attributes
	 */
	protected $attributes;

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
			'current' => false,
			'class'   => [],
			'href'    => null,
		], $properties);

		$this->id         = Arr::pull($properties, 'id');
		$this->title      = Arr::pull($properties, 'title');
		$this->current    = Arr::pull($properties, 'current');
		$this->attributes = $this->getAttributes($properties);
	}

	/**
	 * @inheritDoc
	 */
	public function id(): int
	{
		return $this->id;
	}

	/**
	 * @inheritDoc
	 */
	public function title(): string
	{
		return $this->title;
	}

	/**
	 * @inheritDoc
	 */
	public function url(): ?string
	{
		return $this->attributes['href'];
	}

	/**
	 * @inheritDoc
	 */
	public function classes(): Classes
	{
		return $this->attributes->classes();
	}

	/**
	 * @inheritDoc
	 */
	public function attributes(): Attributes
	{
		return $this->attributes;
	}

	/**
	 * @inheritDoc
	 */
	public function is_disabled(): bool
	{
		return empty($this->attributes['href']);
	}

	/**
	 * @inheritDoc
	 */
	public function is_current(): bool
	{
		return $this->current;
	}

	/**
	 * @param array $attributes
	 *
	 * @return Attributes
	 */
	protected function getAttributes(array $attributes): Attributes
	{
		$attributes['class'] = $this->getClasses($attributes['class']);

		return new Attributes($attributes);
	}

	/**
	 * @param array $classes
	 *
	 * @return Classes
	 */
	abstract protected function getClasses(array $classes): Classes;

}
