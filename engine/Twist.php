<?php

namespace Twist;

include_once __DIR__ . '/app.php';

use Twist\App\App;
use Twist\App\Asset;
use Twist\App\Config;
use Twist\App\Theme;
use Twist\View\ViewInterface;

/**
 * Class Twist
 *
 * @package Twist
 */
class Twist
{

	/**
	 * @param null|string $id
	 *
	 * @return App|mixed
	 */
	public static function app(string $id = null)
	{
		return app($id);
	}

	/**
	 * @param null|string $key
	 * @param null|mixed  $default
	 *
	 * @return Config|mixed
	 */
	public static function config(string $key = null, $default = null)
	{
		return config($key, $default);
	}

	/**
	 * @return Theme
	 */
	public static function theme(): Theme
	{
		return theme();
	}

	/**
	 * @param null|string $template
	 * @param array       $data
	 * @param bool        $renderOnly
	 *
	 * @return ViewInterface|string
	 */
	public static function view(string $template = null, array $data = [], bool $renderOnly = false)
	{
		return view($template, $data, $renderOnly);
	}

	/**
	 * @return Asset
	 */
	public static function asset(): Asset
	{
		return asset();
	}

}