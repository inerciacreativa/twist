<?php

namespace Twist;

use Twist\App\App;
use Twist\App\AppServiceProvider;
use Twist\View\ViewServiceProvider;
use Twist\App\Config;
use Twist\App\Theme;
use Twist\Library\Data\JsonFile;
use Twist\View\ViewInterface;

/**
 * @param null|string $id
 *
 * @return App|mixed
 */
function app(string $id = null)
{
	static $app;

	if ($app === null) {
		$app = (new App())
			->provider(new AppServiceProvider())
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

/**
 * @param bool $fromParentTheme
 *
 * @return JsonFile
 */
function manifest(bool $fromParentTheme = false): JsonFile
{
	static $manifest = [];

	$base = $fromParentTheme ? 'template' : 'stylesheet';

	if (!array_key_exists($base, $manifest)) {
		$manifest[$base] = new JsonFile(config("dir.$base") . '/assets/assets.json');
	}

	return $manifest[$base];
}

/**
 * @param string $filename
 * @param bool   $fromParentTheme
 * @param bool   $fromSource
 *
 * @return string
 */
function asset_url(string $filename, bool $fromParentTheme = false, bool $fromSource = false): string
{
	$base = $fromParentTheme ? 'template' : 'stylesheet';
	$type = $fromSource ? 'source' : 'assets';
	$file = $fromSource ? $filename : manifest($fromParentTheme)->get(ltrim($filename, '/'), $filename);

	return config("uri.$base") . "/$type/$file";
}

/**
 * @param string $filename
 * @param bool   $fromParentTheme
 * @param bool   $fromSource
 *
 * @return string
 */
function asset_path(string $filename, bool $fromParentTheme = false, bool $fromSource = false): string
{
	$base = $fromParentTheme ? 'template' : 'stylesheet';
	$type = $fromSource ? 'source' : 'assets';
	$file = $fromSource ? $filename : manifest($fromParentTheme)->get($filename, $filename);

	return config("dir.$base") . "/$type/$file";
}

/**
 * @param callable $function
 *
 * @return string
 */
function capture(callable $function): string
{
	ob_start();
	$function();

	return ob_get_clean();
}