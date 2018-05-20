<?php

namespace Twist;

use Twist\App\App;
use Twist\App\AppServiceProvider;
use Twist\App\Config;
use Twist\App\Theme;
use Twist\View\ViewInterface;
use Twist\View\ViewServiceProvider;

/**
 * @param null|string $id
 *
 * @return App|mixed
 */
function app(string $id = null)
{
	static $app;

	if ($app === null) {
		$app = (new App())->provider(new AppServiceProvider())
		                  ->provider(new ViewServiceProvider());
	}

	return $id === null ? $app : $app[$id];
}

/**
 * @param string     $key
 * @param null|mixed $default
 *
 * @return Config|mixed
 */
function config(string $key = null, $default = null)
{
	if ($key === null) {
		return app('config');
	}

	return app('config')->get($key, $default);
}

/**
 * @return Theme
 */
function theme()
{
	return app('theme');
}

/**
 * @param null|string $template
 * @param array       $data
 * @param bool        $renderOnly
 *
 * @return ViewInterface|string
 */
function view(string $template = null, array $data = [], bool $renderOnly = false)
{
	if ($template === null) {
		return app('view');
	}

	if ($renderOnly) {
		return app('view')->render($template, $data);
	}

	return app('view')->display($template, $data);
}
