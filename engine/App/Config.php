<?php

namespace Twist\App;

use Twist\Library\Data\Repository;
use Twist\Library\Util\Data;

/**
 * Class Config
 *
 * @package Twist\App
 */
class Config extends Repository
{

    /**
     * Load a config file in JSON format.
     *
     * @param string $file The name of the config file.
     *
     * @return static
     */
    public function load(string $file)
    {
        if (is_file($file) && ($values = file_get_contents($file))) {
            $this->fill(json_decode($values, true));
        }

        return $this;
    }

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