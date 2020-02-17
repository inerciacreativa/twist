<?php

namespace Twist\Model\Link;

use Twist\Library\Html\Classes;
use Twist\Model\Base\CollectionInterface;
use Twist\Model\Base\Model;

/**
 * Class Link
 *
 * @package Twist\Model\Link
 *
 * @method Link|null parent()
 */
class Link extends Model
{

	/**
	 * @var array
	 */
	protected $properties;

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
			'url'     => null,
			'classes' => [],
			'label'   => null,
			'rel'     => null,
		], $properties);

		if (!($properties['classes'] instanceof Classes)) {
			$properties['classes'] = new Classes($properties['classes']);
		}

		$this->properties = $properties;
	}

	/**
	 * @inheritdoc
	 *
	 * @return Links
	 */
	public function children(): ?CollectionInterface
	{
		if ($this->children === null) {
			$this->set_children(new Links($this));
		}

		return $this->children;
	}

	/**
	 * @inheritdoc
	 */
	public function id(): int
	{
		return (int) $this->properties['id'];
	}

	/**
	 * @return string
	 */
	public function title(): string
	{
		return $this->properties['title'];
	}

	/**
	 * @return null|string
	 */
	public function url(): ?string
	{
		return $this->properties['url'];
	}

	/**
	 * @param string|array $class
	 *
	 * @return Classes
	 */
	public function classes($class = []): Classes
	{
		return $this->properties['classes']->add($class);
	}

	/**
	 * @return null|string
	 */
	public function label(): ?string
	{
		return $this->properties['label'];
	}

	/**
	 * @return null|string
	 */
	public function rel(): ?string
	{
		return $this->properties['rel'];
	}

	/**
	 * @return bool
	 */
	public function is_disabled(): bool
	{
		return $this->properties['url'] === null;
	}

	/**
	 * @return bool
	 */
	public function is_current(): bool
	{
		return $this->properties['classes']->has('current');
	}

	/**
	 * @return bool
	 */
	public function is_next(): bool
	{
		return $this->properties['classes']->has('next');
	}

	/**
	 * @return bool
	 */
	public function is_previous(): bool
	{
		return $this->properties['classes']->has('prev');
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->title();
	}
}
