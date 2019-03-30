<?php

namespace Twist\Library\Hook;

/**
 * Class ClosureAction
 *
 * @package Twist\Library\Hook
 */
class ClosureAction extends Action
{

	/**
	 * ClosureAction constructor.
	 *
	 * @param string   $hook
	 * @param callable $callback
	 * @param array    $parameters {
	 *
	 * @type string    $id
	 * @type int       $priority
	 * @type int       $arguments
	 * @type bool      $enabled
	 * }
	 */
	public function __construct($hook, callable $callback, array $parameters = [])
	{
		$id = array_key_exists('id', $parameters) ? $parameters['id'] : spl_object_hash($this);

		parent::__construct($hook, $callback, $parameters);

		$this->setId('global', $id);
	}

}