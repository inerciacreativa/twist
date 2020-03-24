<?php

namespace Twist;

use Twist\View\Context;

/**
 * Class View
 *
 * @package Twist
 */
class View
{

	/**
	 * @return Context
	 */
	final public static function context(): Context
	{
		return Twist::app('context');
	}

	/**
	 * @param string $template
	 * @param array  $data
	 */
	final public static function display(string $template = null, array $data = []): void
	{
		Twist::app('view')->display($template, $data);
	}

	/**
	 * @param string $template
	 * @param array  $data
	 *
	 * @return string
	 */
	final public static function render(string $template, array $data = []): string
	{
		return Twist::app('view')->render($template, $data);
	}

	/**
	 * @param string      $path
	 * @param string|null $namespace
	 */
	final public static function path(string $path, string $namespace = null): void
	{
		Twist::app('view')->path($path, $namespace);
	}

}
