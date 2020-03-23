<?php

namespace Twist\Asset;

use Twist\Library\Data\Repository;
use Twist\Library\Support\Json;
use Twist\Library\Support\Url;
use Twist\App\Config;
use RuntimeException;
use Twist\App\Theme;

/**
 * Class Manifest
 *
 * @package Twist\Asset
 */
class Manifest
{

	/**
	 * @var Repository[]
	 */
	private $manifest = [];

	/**
	 * @var Config
	 */
	private $config;

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
	 * @return Repository
	 */
	private function manifest(string $theme, string $filename): Repository
	{
		if (!array_key_exists($theme, $this->manifest)) {
			$path = $this->config->get("dir.$theme");

			try {
				$manifest = Json::load($path . $filename);
			} catch (RuntimeException $exception) {
				$manifest = new Repository();
			}

			$this->manifest[$theme] = $manifest;
		}

		return $this->manifest[$theme];
	}

	/**
	 * @param string $filename
	 * @param bool   $parent
	 *
	 * @return array
	 */
	private function get(string $filename, bool $parent): array
	{
		$theme    = $parent ? Theme::PARENT : Theme::CHILD;
		$config   = $this->config->get("asset.$theme", [
			'path'     => '/',
			'manifest' => '',
		]);
		$manifest = $config['path'] . $config['manifest'];
		$filename = $config['path'] . $this->manifest($theme, $manifest)
										   ->get($filename, $filename);

		return [
			$theme,
			$filename,
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
