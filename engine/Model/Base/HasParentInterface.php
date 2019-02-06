<?php

namespace Twist\Model\Base;

/**
 * Interface HasParentInterface
 *
 * @package Twist\Model\Base
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