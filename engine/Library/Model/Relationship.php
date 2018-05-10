<?php

namespace Twist\Library\Model;

trait Relationship
{

	/**
	 * @var ModelInterface
	 */
	protected $parent;

	/**
	 * @var CollectionInterface
	 */
	protected $children;

	protected function set_parent(ModelInterface $parent): void
	{
		$this->parent = $parent;
	}

	public function has_parent(): bool
	{
		return $this->parent !== null;
	}

	public function parent(): ?ModelInterface
	{
		return $this->parent;
	}

	protected function set_children(CollectionInterface $children): void
	{
		$this->children = $children;
	}

	public function has_children(): bool
	{
		return $this->children !== null && $this->children->count() > 0;
	}

	public function children(): ?CollectionInterface
	{
		return $this->children;
	}

}