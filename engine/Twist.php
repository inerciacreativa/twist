<?php

namespace Twist;

use Twist\App\App;
use Twist\App\AppServiceProvider;
use Twist\App\Config;
use Twist\App\Theme;
use Twist\Service\ServiceProviderInterface;
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
	 * @return App
	 */
	final public static function app(): App
	{
		if (self::$app === null) {
			self::$app = self::create();
		}

		return self::$app;
	}

	/**
	 * @param string $id
	 *
	 * @return mixed
	 */
	final public static function service(string $id)
	{
		return self::app()[$id];
	}

	/**
	 * @param ServiceProviderInterface $provider
	 */
	final public static function provider(ServiceProviderInterface $provider): void
	{
		self::app()->provider($provider);
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
			return self::service('config');
		}

		return self::service('config')->get($key, $default);
	}

	/**
	 * @return Theme
	 */
	final public static function theme(): Theme
	{
		return self::service('theme');
	}

	/**
	 * @return ViewInterface
	 */
	final public static function view(): ViewInterface
	{
		return self::service('view');
	}

	/**
	 * @return bool
	 */
	final public static function isDevelopment(): bool
	{
		return (defined('WP_ENV') && WP_ENV === 'development') || class_exists('WP_CLI');
	}

	/**
	 * @return bool
	 */
	final public static function isDebug(): bool
	{
		return defined('WP_DEBUG') && WP_DEBUG;
	}

	/**
	 * @return App
	 */
	private static function create(): App
	{
		return (new App())->provider(new AppServiceProvider())
						  ->provider(new ViewServiceProvider());
	}

}
