<?php

namespace Twist\View;

use Twist\Service\Service;
use Twist\Library\Util\Data;


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
			$this->data($name, $this->getData($value));
		}
	}

	/**
	 * @param mixed $data
	 *
	 * @return mixed
	 */
	protected function getData($data)
	{
		if (\is_string($data) && class_exists($data)) {
			return new $data();
		}

		return Data::value($data);
	}

}