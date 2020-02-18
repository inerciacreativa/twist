<?php

namespace Twist\Model;

/**
 * Interface HasParentInterface
 *
 * @package Twist\Model
 */
interface HasParentInterface
{

	/**
	 * @param ModelInterface $parent
	 */
	public function set_parent(ModelInterface $parent): void;

	/**
	 * @return bool
	 */
	public function has_parent(): bool;

	/**
	 * @return null|ModelInterface
	 */
	public function parent(): ?ModelInterface;

}
