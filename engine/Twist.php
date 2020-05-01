<?php

namespace Twist;

use Monolog\Logger;
use Twist\App\App;
use Twist\App\AppServiceProvider;
use Twist\App\Config;
use Twist\App\Theme;
use Twist\Asset\AssetServiceProvider;
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

	public const DEVELOPMENT = 'development';

	public const STAGING = 'staging';

	public const PRODUCTION = 'production';

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
	 * @return Logger
	 */
	final public static function logger(): Logger
	{
		return self::service('logger');
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
	 * @return string
	 */
	final public static function env(): string
	{
		if (class_exists('WP_CLI')) {
			return self::DEVELOPMENT;
		}

		if (defined('WP_ENV')) {
			return WP_ENV;
		}

		return self::PRODUCTION;
	}

	/**
	 * @param string|array $env
	 *
	 * @return bool
	 */
	final public static function isEnv($env): bool
	{
		return in_array(self::env(), (array) $env, true);
	}

	/**
	 * @return bool
	 */
	final public static function isDebug(): bool
	{
		return defined('WP_DEBUG') && WP_DEBUG;
	}

	/**
	 * @return bool
	 */
	final public static function isAdmin(): bool
	{
		return is_admin();
	}

	/**
	 * @return App
	 */
	private static function create(): App
	{
		return (new App())->provider(new AppServiceProvider())
						  ->provider(new AssetServiceProvider())
						  ->provider(new ViewServiceProvider());
	}

}
