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
	 * @return bool
	 */
	public function has_parent(): bool;

	/**
	 * @return null|ModelInterface
	 */
	public function parent(): ?ModelInterface;

}