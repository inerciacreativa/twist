<?php

namespace Twist\Library\Model;

/**
 * Trait HasChildren
 *
 * @package Twist\Library\Model
 */
trait HasChildren
{

	/**
	 * @var CollectionInterface
	 */
	protected $children;

	/**
	 * @param CollectionInterface $children
	 */
	protected function set_children(CollectionInterface $children): void
	{
		$this->children = $children;
	}

	/**
	 * @return bool
	 */
	public function has_children(): bool
	{
		return $this->children !== null && $this->children->count() > 0;
	}

	/**
	 * @return null|CollectionInterface
	 */
	public function children(): ?CollectionInterface
	{
		return $this->children;
	}

}