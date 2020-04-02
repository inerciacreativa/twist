<?php

namespace Twist\Model\Navigation;

use Twist\Model\CollectionInterface;
use Twist\Model\HasChildren;
use Twist\Model\HasChildrenInterface;
use Twist\Model\Link\Link as BaseLink;
use Twist\Model\Link\Links;

/**
 * Class Link
 *
 * @package Twist\Model\Navigation
 *
 * @method Link|null parent()
 */
class Link extends BaseLink implements HasChildrenInterface
{

	use HasChildren;

	/**
	 * @inheritDoc
	 */
	public function __construct(array $properties)
	{
		$properties = array_merge([
			'rel' => null,
		], $properties);

		parent::__construct($properties);
	}

	/**
	 * @inheritDoc
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
	 * @return string|null
	 */
	public function rel(): ?string
	{
		return $this->attributes['rel'];
	}

}
