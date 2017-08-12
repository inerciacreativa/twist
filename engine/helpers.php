<?php

namespace Twist;

use Twist\App\Application;
use Twist\App\Config;
use Twist\Library\Data\JsonFile;
use Twist\View\ViewServiceInterface;

/**
 * @param null|string $id
 *
 * @return Application|mixed
 */
function app(string $id = null)
{
	static $container;

	if ($container === null) {
		$container = new Application();
	}

	return $id === null ? $container : $container[$id];
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
 * @return ViewServiceInterface|string
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
 * @param string $filename
 * @param bool   $parent
 *
 * @return string
 */
function asset(string $filename, bool $parent = false): string
{
	static $manifest;

	$source = $parent ? 'template' : 'stylesheet';
	$path   = config("uri.$source") . '/assets/' . dirname($filename) . '/';
	$file   = basename($filename);

	if ($manifest === null) {
		$manifest = new JsonFile(config("dir.$source") . '/assets/assets.json');
	}

	return $path . $manifest->get($file, $file);
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