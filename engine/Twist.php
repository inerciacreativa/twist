<?php

namespace Twist;

use Twist\App\App;
use Twist\App\AppServiceProvider;
use Twist\App\Asset;
use Twist\App\Config;
use Twist\App\Context;
use Twist\App\Theme;
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
	 * @return Asset
	 */
	final public static function asset(): Asset
	{
		return self::app('asset');
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