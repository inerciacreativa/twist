<?php

namespace Twist\Model;

/**
 * Trait HasParent
 *
 * @package Twist\Model
 */
trait HasParent
{

	/**
	 * @var ModelInterface
	 */
	protected $parent;

	/**
	 * @param ModelInterface $parent
	 */
	public function set_parent(ModelInterface $parent): void
	{
		$this->parent = $parent;
	}

	/**
	 * @return bool
	 */
	public function has_parent(): bool
	{
		return $this->parent !== null;
	}

	/**
	 * @return null|ModelInterface
	 */
	public function parent(): ?ModelInterface
	{
		return $this->parent;
	}

}
