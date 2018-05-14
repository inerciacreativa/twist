<?php

namespace Twist\Library\Model;

/**
 * Interface HasParentInterface
 *
 * @package Twist\Library\Model
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