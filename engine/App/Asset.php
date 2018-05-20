<?php

namespace Twist\App;

use Twist\Library\Data\JsonFile;

class Asset
{

	protected const FILE = '/assets/assets.json';

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
	 * @param bool $fromParentTheme
	 *
	 * @return JsonFile
	 */
	protected function manifest(bool $fromParentTheme): JsonFile
	{
		$base = $fromParentTheme ? 'template' : 'stylesheet';

		if (!array_key_exists($base, $this->manifest)) {
			$path = $this->config->get("dir.$base");

			$this->manifest[$base] = new JsonFile($path . self::FILE);
		}

		return $this->manifest[$base];
	}

	protected function get(string $filename, bool $fromParentTheme)
	{
		return $this->manifest($fromParentTheme)->get(ltrim($filename, '/'), $filename);
	}

	/**
	 * @param string $filename
	 * @param bool   $fromParentTheme
	 * @param bool   $fromSource
	 *
	 * @return string
	 */
	public function url(string $filename, bool $fromParentTheme = false, bool $fromSource = false): string
	{
		$base = $fromParentTheme ? 'template' : 'stylesheet';
		$type = $fromSource ? 'source' : 'assets';
		$file = $fromSource ? $filename : $this->get($filename, $fromParentTheme);

		return $this->config->get("uri.$base") . "/$type/$file";
	}

	/**
	 * @param string $filename
	 * @param bool   $fromParentTheme
	 * @param bool   $fromSource
	 *
	 * @return string
	 */
	public function path(string $filename, bool $fromParentTheme = false, bool $fromSource = false): string
	{
		$base = $fromParentTheme ? 'template' : 'stylesheet';
		$type = $fromSource ? 'source' : 'assets';
		$file = $fromSource ? $filename : $this->get($filename, $fromParentTheme);

		return $this->config->get("dir.$base") . "/$type/$file";
	}

}