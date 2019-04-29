<?php

namespace Twist\Library\Hook;

/**
 * Trait Hookable
 *
 * @package Twist\Library\Hook
 */
trait Hookable
{

	/**
	 * @return Hook
	 */
	protected function hook(): Hook
	{
		return Hook::bind($this);
	}

}