<?php

namespace Twist\Library\Model;

/**
 * Interface HasChildrenInterface
 *
 * @package Twist\Library\Model
 */
interface HasChildrenInterface
{

	/**
	 * @return bool
	 */
	public function has_children(): bool;

	/**
	 * @return null|CollectionInterface
	 */
	public function children(): ?CollectionInterface;

}