<?php

namespace Twist\Model\Navigation;

use Twist\Library\Model\CollectionInterface;
use Twist\Library\Model\Model;

/**
 * Class Link
 *
 * @package Twist\Model\Navigation
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

		if (\is_string($properties['classes'])) {
			$properties['classes'] = (array) preg_split('#\s+#', $properties['classes']);
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
	 * @return string
	 */
	public function classes(): string
	{
		return trim(implode(' ', (array) $this->properties['classes']));
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