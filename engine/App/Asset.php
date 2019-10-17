<?php

namespace Twist\App;

use Twist\Library\Data\JsonFile;
use Twist\Library\Support\Url;

/**
 * Class Asset
 *
 * @package Twist\App
 */
class Asset
{

	public const PARENT = 'template';

	public const CHILD = 'stylesheet';

	/**
	 * @var JsonFile[]
	 */
	protected $manifest = [];

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * Asset constructor.
	 *
	 * @param Config $config
	 */
	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	/**
	 * @param string $theme
	 * @param string $filename
	 *
	 * @return JsonFile
	 */
	protected function manifest(string $theme, string $filename): JsonFile
	{
		if (!array_key_exists($theme, $this->manifest)) {
			$path = $this->config->get("dir.$theme");

			$this->manifest[$theme] = new JsonFile($path . $filename);
		}

		return $this->manifest[$theme];
	}

	/**
	 * @param string $filename
	 * @param bool   $parent
	 *
	 * @return array
	 */
	protected function get(string $filename, bool $parent): array
	{
		$theme  = $parent ? self::PARENT : self::CHILD;
		$config = $this->config->get("asset.$theme", [
			'source'   => '/',
			'target'   => '/',
			'manifest' => '',
		]);

		$manifest = $config['target'] . $config['manifest'];
		$filename = $this->manifest($theme, $manifest)
						 ->get($filename, $filename);

		$path = $this->config->get("dir.$theme") . $config['target'] . $filename;
		$file = file_exists($path) ? ($config['target'] . $filename) : ($config['source'] . $filename);

		return [
			$theme,
			$file,
		];
	}

	/**
	 * @param string $filename
	 * @param bool   $parent
	 *
	 * @return string
	 */
	public function url(string $filename, bool $parent = false): string
	{
		$url = Url::parse($filename);
		if ($url->isValid() && $url->isAbsolute()) {
			return $filename;
		}

		[
			$theme,
			$file,
		] = $this->get($filename, $parent);

		return $this->config->get("uri.$theme") . $file;
	}

	/**
	 * @param string $filename
	 * @param bool   $parent
	 *
	 * @return string
	 */
	public function path(string $filename, bool $parent = false): string
	{
		[
			$theme,
			$file,
		] = $this->get($filename, $parent);

		return $this->config->get("dir.$theme") . $file;
	}

}
