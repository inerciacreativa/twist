<?php

namespace Twist;

use Twist\App\Application;
use Twist\App\Config;
use Twist\Library\Data\JsonFile;
use Twist\View\ViewInterface;

/**
 * @param null|string $id
 *
 * @return Application|mixed
 */
function app(string $id = null)
{
	static $app;

	if ($app === null) {
		$app = new Application();
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
 * @param null|string $template
 * @param array       $data
 * @param bool        $render
 *
 * @return ViewInterface|string
 */
function view(string $template = null, array $data = [], bool $render = false)
{
	if ($template === null) {
		return app('view');
	}

	if ($render) {
		return app('view')->render($template, $data);
	}

	return app('view')->display($template, $data);
}

/**
 * @param bool $parent
 *
 * @return JsonFile
 */
function manifest(bool $parent = false): JsonFile
{
	static $manifest = [];

	$base = $parent ? 'template' : 'stylesheet';

	if (!array_key_exists($base, $manifest)) {
		$manifest[$base] = new JsonFile(config("dir.$base") . '/assets/assets.json');
	}

	return $manifest[$base];
}

/**
 * @param string $filename
 * @param bool   $parent
 * @param bool   $source
 *
 * @return string
 */
function asset_url(string $filename, bool $parent = false, bool $source = false): string
{
	$base = $parent ? 'template' : 'stylesheet';
	$type = $source ? 'source' : 'assets';
	$file = $source ? $filename : manifest($parent)->get($filename, $filename);

	return config("uri.$base") . "/$type/$file";
}

/**
 * @param string $filename
 * @param bool   $parent
 * @param bool   $source
 *
 * @return string
 */
function asset_path(string $filename, bool $parent = false, bool $source = false): string
{
	$base = $parent ? 'template' : 'stylesheet';
	$type = $source ? 'source' : 'assets';
	$file = $source ? $filename : manifest($parent)->get($filename, $filename);

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