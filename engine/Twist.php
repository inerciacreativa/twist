<?php

namespace Twist;

use Twist\App\App;
use Twist\App\AppServiceProvider;
use Twist\App\Config;
use Twist\App\Theme;
use Twist\Asset\Manifest;
use Twist\Asset\Queue;
use Twist\View\ViewInterface;
use Twist\View\ViewServiceProvider;

/**
 * Class Twist
 *
 * @package Twist
 */
class Twist
{

	/**
	 * @var App
	 */
	private static $app;

	/**
	 * @param null|string $id
	 *
	 * @return App|mixed
	 */
	final public static function app(string $id = null)
	{
		if (self::$app === null) {
			self::$app = (new App())->provider(new AppServiceProvider())
									->provider(new ViewServiceProvider());
		}

		return $id === null ? self::$app : self::$app[$id];
	}

	/**
	 * @param null|string $key
	 * @param null|mixed  $default
	 *
	 * @return Config|mixed
	 */
	final public static function config(string $key = null, $default = null)
	{
		if ($key === null) {
			return self::app('config');
		}

		return self::app('config')->get($key, $default);
	}

	/**
	 * @return Theme
	 */
	final public static function theme(): Theme
	{
		return self::app('theme');
	}

	/**
	 * @return Manifest
	 */
	final public static function manifest(): Manifest
	{
		return self::app('asset_manifest');
	}

	/**
	 * @return Queue
	 */
	final public static function queue(): Queue
	{
		return self::app('asset_queue');
	}

	/**
	 * @return ViewInterface
	 */
	final public static function view(): ViewInterface
	{
		return self::app('view');
	}

}
