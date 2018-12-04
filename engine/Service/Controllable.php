<?php

namespace Twist\Service;

use Twist\Library\Hook\Hookable;

/**
 * Trait Controllable
 *
 * @package Twist\Service
 */
trait Controllable
{

	use Hookable;

	/**
	 * @var bool
	 */
	private $enabled = false;

	/**
	 * @inheritdoc
	 */
	public function enable(): void
	{
		if (!$this->enabled) {
			$this->hook()->enable();

			$this->enabled = true;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function disable(): void
	{
		if ($this->enabled) {
			$this->hook()->disable();

			$this->enabled = false;
		}
	}

}