<?php

namespace Twist;

use Twist\View\Context;
use Twist\View\ViewInterface;

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
		return Twist::service('context');
	}

	/**
	 * @param string $template
	 * @param array  $data
	 *
	 * @see ViewInterface::display()
	 */
	final public static function display(string $template = null, array $data = []): void
	{
		Twist::service('view')->display($template, $data);
	}

	/**
	 * @param string $template
	 * @param array  $data
	 *
	 * @return string
	 *
	 * @see ViewInterface::render()
	 */
	final public static function render(string $template, array $data = []): string
	{
		return Twist::service('view')->render($template, $data);
	}

	/**
	 * @param string      $path
	 * @param string|null $namespace
	 *
	 * @see ViewInterface::path()
	 */
	final public static function path(string $path, string $namespace = null): void
	{
		Twist::service('view')->path($path, $namespace);
	}

}
