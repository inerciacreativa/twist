<?php

namespace Twist\View;

use Twist\Service\Service;

/**
 * Class ViewService
 *
 * @package Twist\View
 */
abstract class ViewService extends Service implements ViewInterface
{

	/**
	 * @inheritdoc
	 */
	public function boot()
	{
		$data = $this->config->get('view.data', []);

		foreach ((array) $data as $name => $value) {
			$this->data($name, \is_string($value) && class_exists($value) ? new $value() : $value);
		}
	}

}