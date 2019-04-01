<?php

namespace Twist\View\Twig;

use Kint;
use Twig_Extension;
use Twig_Filter;
use Twig_SimpleFunction;

/**
 * Class TwigExtension
 *
 * @package Twist\View\Twig
 */
class TwigExtension extends Twig_Extension
{

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return 'twist';
	}

	/**
	 * @return array
	 */
	public function getFilters(): array
	{
		return [
			new Twig_Filter('classes', static function (array $classes) {
				return implode(' ', array_filter($classes));
			}),
		];
	}

	/**
	 * @return array
	 */
	public function getFunctions(): array
	{
		return [
			new Twig_SimpleFunction('kint', static function ($debug) {
				Kint::dump($debug);
			}, ['is_safe' => ['html']]),
			new Twig_SimpleFunction('__', static function (string $string) {
				$translation = __($string, 'twist');

				if (func_num_args() > 1) {
					$arguments    = func_get_args();
					$arguments[0] = $translation;
					$translation  = sprintf(...$arguments);
				}

				return $translation;
			}, ['is_safe' => ['html']]),
			new Twig_SimpleFunction('_x', static function (string $string, string $context) {
				$translation = _x($string, $context, 'twist');

				if (func_num_args() > 2) {
					$arguments    = func_get_args();
					$arguments[0] = $translation;
					array_splice($arguments, 1, 1);
					$translation = sprintf(...$arguments);
				}

				return $translation;
			}, ['is_safe' => ['html']]),
			new Twig_SimpleFunction('_n', static function (string $single, string $plural, $number) {
				$translation = _n($single, $plural, $number, 'twist');

				return sprintf($translation, $number);
			}, ['is_safe' => ['html']]),
			new Twig_SimpleFunction('_nx', static function (string $single, string $plural, $number, string $context) {
				$translation = _nx($single, $plural, $number, $context, 'twist');

				return sprintf($translation, $number);
			}, ['is_safe' => ['html']]),
		];
	}

}