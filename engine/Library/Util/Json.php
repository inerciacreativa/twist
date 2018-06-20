<?php

namespace Twist\Library\Util;

/**
 * Class Json
 *
 * @package Twist\Library\Util
 */
class Json
{

	/**
	 * @param mixed $value
	 * @param int   $options
	 * @param int   $depth
	 *
	 * @return string
	 */
	public static function encode($value, int $options = 0, int $depth = 512): string
	{
		$json = \json_encode($value, $options, $depth);
		if (JSON_ERROR_NONE !== json_last_error()) {
			throw new \InvalidArgumentException('json_encode error: ' . json_last_error_msg());
		}

		return $json;
	}

	/**
	 * @param string $json
	 * @param bool   $assoc
	 * @param int    $depth
	 * @param int    $options
	 *
	 * @return array|mixed|object
	 */
	public static function decode(string $json, bool $assoc = false, int $depth = 512, int $options = 0)
	{
		$data = \json_decode($json, $assoc, $depth, $options);
		if (JSON_ERROR_NONE !== json_last_error()) {
			throw new \InvalidArgumentException('json_decode error: ' . json_last_error_msg());
		}

		return $data;
	}

}