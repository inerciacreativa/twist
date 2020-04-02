<?php

namespace Twist\Model\Link;

use Twist\Library\Html\Attributes;
use Twist\Library\Html\Classes;
use Twist\Library\Support\Arr;
use Twist\Model\HasParent;
use Twist\Model\HasParentInterface;
use Twist\Model\ModelInterface;

/**
 * Class Link
 *
 * @package Twist\Model\Link
 *
 * @method Link|null parent()
 */
abstract class Link implements ModelInterface, HasParentInterface
{

	use HasParent;

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
		$this->attributes = new Attributes($properties);
	}

	/**
	 * @inheritDoc
	 */
	public function id(): int
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function title(): string
	{
		return $this->title;
	}

	/**
	 * @return string|null
	 */
	public function url(): ?string
	{
		return $this->attributes['href'];
	}

	/**
	 * @return Classes
	 */
	public function classes(): Classes
	{
		return $this->attributes->classes();
	}

	/**
	 * @return Attributes
	 */
	public function attributes(): Attributes
	{
		return $this->attributes;
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
		return $this->current;
	}

}
