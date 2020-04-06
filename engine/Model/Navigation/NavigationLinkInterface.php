<?php

namespace Twist\Model\Navigation;

use Twist\Model\HasChildrenInterface;
use Twist\Model\HasParentInterface;
use Twist\Model\Link\LinkInterface;

/**
 * Interface NavigationLinkInterface
 *
 * @package Twist\Model\Navigation
 */
interface NavigationLinkInterface extends LinkInterface, HasParentInterface, HasChildrenInterface
{

	/**
	 * @return string|null
	 */
	public function rel(): ?string;

}
