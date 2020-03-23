<?php

namespace Twist;

use Twist\App\App;
use Twist\App\AppServiceProvider;
use Twist\App\Assets;
use Twist\App\AssetsQueue;
use Twist\App\Config;
use Twist\App\Theme;
use Twist\Asset\Manifest;
use Twist\Asset\Queue;
use Twist\View\Context;
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
	 * @return Context
	 */
	final public static function context(): Context
	{
		return self::app('context');
	}

	/**
	 * @param null|string $template
	 * @param array       $data
	 * @param bool        $renderOnly
	 *
	 * @return ViewInterface|string
	 */
	final public static function view(string $template = null, array $data = [], bool $renderOnly = false)
	{
		if ($template === null) {
			return self::app('view');
		}

		if ($renderOnly) {
			return self::app('view')->render($template, $data);
		}

		return self::app('view')->display($template, $data);
	}

}
