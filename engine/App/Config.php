<?php

namespace Twist\App;

use Twist\Library\Data\Repository;
use Twist\Library\Support\Data;

/**
 * Class Config
 *
 * @package Twist\App
 */
class Config extends Repository
{

	/**
	 * Make sure that returns a value (and not a closure).
	 *
	 * @inheritdoc
	 */
	public function get(string $key, $default = null)
	{
		return Data::value(parent::get($key, $default));
	}

}