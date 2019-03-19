<?php

namespace Twist\App;

use Twist\Library\Data\JsonFile;
use Twist\Library\Util\Url;

/**
 * Class Asset
 *
 * @package Twist\App
 */
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

	/**
	 * @param string $filename
	 * @param bool   $fromParentTheme
	 *
	 * @return mixed
	 */
	protected function get(string $filename, bool $fromParentTheme)
	{
		return $this->manifest($fromParentTheme)
		            ->get(ltrim($filename, '/'), $filename);
	}

	/**
	 * @param string $filename
	 * @param bool   $fromParentTheme
	 * @param bool   $fromSource
	 *
	 * @return array
	 */
	protected function resolve(string $filename, bool $fromParentTheme = false, bool $fromSource = false): array
	{
		return [
			$fromParentTheme ? 'template' : 'stylesheet',
			$fromSource ? 'source' : 'assets',
			$fromSource ? $filename : $this->get($filename, $fromParentTheme),
		];
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
		$url = Url::parse($filename);
		if ($url->isValid() && $url->isAbsolute()) {
			return $filename;
		}

		[
			$base,
			$type,
			$file,
		] = $this->resolve($filename, $fromParentTheme, $fromSource);

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
		[
			$base,
			$type,
			$file,
		] = $this->resolve($filename, $fromParentTheme, $fromSource);

		return $this->config->get("dir.$base") . "/$type/$file";
	}

}