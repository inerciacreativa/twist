<?php

namespace Twist\Model\Navigation;

use Twist\Model\CollectionInterface;
use Twist\Model\Link\Link as BaseLink;
use Twist\Model\Link\Links;

/**
 * Class Link
 *
 * @package Twist\Model\Navigation
 *
 * @method Link|null parent()
 */
class Link extends BaseLink
{

	/**
	 * @inheritdoc
	 */
	public function __construct(array $properties)
	{
		$properties = array_merge([
			'rel' => null,
		], $properties);

		parent::__construct($properties);
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
	 * @return null|string
	 */
	public function rel(): ?string
	{
		return $this->attributes['rel'];
	}

}
