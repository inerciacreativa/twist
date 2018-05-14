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
	 * @param CollectionInterface $children
	 */
	public function set_children(CollectionInterface $children): void;

	/**
	 * @return bool
	 */
	public function has_children(): bool;

	/**
	 * @return null|CollectionInterface
	 */
	public function children(): ?CollectionInterface;

}