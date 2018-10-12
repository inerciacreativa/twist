<?php

namespace Twist\View;

use Twist\Library\Util\Data;
use Twist\Service\Service;

/**
 * Class ViewService
 *
 * @package Twist\View
 */
abstract class ViewService extends Service implements ViewInterface
{

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * @inheritdoc
	 */
	public function addData(string $name, $value): ViewInterface
	{
		$this->data[$name] = $value;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getData(): array
	{
		return $this->data;
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	protected function mergeData(array $data): array
	{
		return array_map([
			$this,
			'resolveData',
		], array_merge($this->data, $data));
	}

	/**
	 * @param mixed $data
	 *
	 * @return mixed
	 */
	protected function resolveData($data)
	{
		if (\is_string($data) && class_exists($data)) {
			return new $data();
		}

		return Data::value($data);
	}

}