<?php

namespace Twist\Model\Navigation;

use Twist\Library\Model\Model;
use Twist\Library\Model\CollectionInterface;

/**
 * Class Link
 *
 * @package Twist\Model\Navigation
 */
class Link extends Model
{

	protected $properties;

	/**
	 * Link constructor.
	 *
	 * @param Links $links
	 * @param array $properties
	 */
	public function __construct(Links $links, array $properties)
	{
		$this->properties = $properties;

		if ($links->has_parent()) {
			$this->set_parent($links->parent());
		}
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
		return $this->properties['id'];
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
	 * @return string
	 */
	public function classes(): string
	{
		return trim(implode(' ', $this->properties['classes']));
	}

	/**
	 * @return null|string
	 */
	public function label(): ?string
	{
		return $this->properties['label'] ?? null;
	}

	/**
	 * @return null|string
	 */
	public function rel(): ?string
	{
		return $this->properties['rel'] ?? null;
	}

	/**
	 * @return bool
	 */
	public function is_current(): bool
	{
		return \in_array('current', $this->properties['classes'], true);
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
	public function is_next(): bool
	{
		return \in_array('next', $this->properties['classes'], true);
	}

	/**
	 * @return bool
	 */
	public function is_previous(): bool
	{
		return \in_array('prev', $this->properties['classes'], true);
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->title();
	}
}