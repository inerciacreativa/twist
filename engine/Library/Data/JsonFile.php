<?php

namespace Twist\Library\Data;

use InvalidArgumentException;
use Twist\Library\Util\Arr;
use Twist\Library\Util\Json;

/**
 * Class JsonFile
 *
 * @package Twist\Library\Data
 */
class JsonFile
{

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * JsonFile constructor.
	 *
	 * @param string $file
	 */
	public function __construct(string $file)
	{
		if (file_exists($file) && is_file($file)) {
			try {
				$this->data = Json::decode(file_get_contents($file), true);
			} catch (InvalidArgumentException $exception) {
				$this->data = [];
			}
		}
	}

	/**
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get(string $key, $default = null)
	{
		if (empty($this->data)) {
			return $default;
		}

		return $this->data[$key] ?? Arr::get($this->data, $key, $default);
	}

	/**
	 * @return array|object
	 */
	public function all(): array
	{
		return $this->data;
	}

}