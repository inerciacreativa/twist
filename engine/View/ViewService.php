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
	public function boot(): void
	{
		foreach ((array) $this->config->get('view.global', []) as $name => $value) {
			$this->addGlobalData($name, $this->getData($value));
		}

		foreach ((array) $this->config->get('view.data', []) as $name => $value) {
			$this->addData($name, $this->getData($value));
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