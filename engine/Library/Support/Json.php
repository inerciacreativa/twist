<?php

namespace Twist\Library\Support;

use RuntimeException;
use Twist\Library\Data\Repository;
use Twist\Library\Data\RepositoryInterface;

class Json
{

	/**
	 * @param mixed $value
	 * @param int   $options
	 *
	 * @return string
	 */
	public static function encode($value, int $options = 0): string
	{
		if ($value instanceof RepositoryInterface) {
			$value = $value->all();
		}

		$json = json_encode($value, $options);

		if (JSON_ERROR_NONE !== json_last_error()) {
			throw new RuntimeException('Json::encode error: ' . json_last_error_msg());
		}

		return $json;
	}

	/**
	 * @param string $json
	 * @param int    $options
	 *
	 * @return Repository
	 */
	public static function decode(string $json, int $options = 0): Repository
	{
		$data = json_decode($json, true, 512, $options);

		if (JSON_ERROR_NONE !== json_last_error()) {
			throw new RuntimeException('Json::decode error: ' . json_last_error_msg());
		}

		return new Repository($data);
	}

	/**
	 * @param string $file
	 * @param int    $options
	 *
	 * @return Repository
	 */
	public static function load(string $file, int $options = 0): Repository
	{
		if (!file_exists($file) || !is_file($file)) {
			throw new RuntimeException("Json::load error: '$file' does not exists.");
		}

		$json = file_get_contents($file);
		if ($json === false) {
			throw new RuntimeException("Json::load error: '$file' could not be read.");
		}

		return self::decode($json, $options);
	}

	/**
	 * @param string $file
	 * @param mixed  $value
	 * @param int    $options
	 */
	public static function save(string $file, $value, int $options): void
	{
		$options |= JSON_PRETTY_PRINT;
		$json    = self::encode($value, $options);

		if (file_put_contents($file, $json) === false) {
			throw new RuntimeException("Json::save error: '$file' could not be read.");
		}
	}

}
