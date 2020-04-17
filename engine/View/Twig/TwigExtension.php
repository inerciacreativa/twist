<?php

namespace Twist\View\Twig;

use Kint\Kint;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twist\Library\Html\Attributes;
use Twist\Library\Html\Classes;
use Twist\Library\Support\Str;
use Twist\Twist;

/**
 * Class TwigExtension
 *
 * @package Twist\View\Twig
 */
class TwigExtension extends AbstractExtension
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
			new TwigFilter('classes', static function ($classes) {
				return Classes::make($classes)->render();
			}),
			new TwigFilter('attributes', static function ($attributes) {
				return Attributes::make($attributes)->render();
			}),
			new TwigFilter('normalize', static function (string $content) {
				return trim(preg_replace('/>\s+</', '> <', $content));
			}, ['is_safe' => ['html']]),
			new TwigFilter('words', static function ($content, int $words, string $end = '…') {
				return Str::words($content, $words, $end);
			}, ['is_safe' => ['html']]),
		];
	}

	/**
	 * @return array
	 */
	public function getFunctions(): array
	{
		$functions = [
			new TwigFunction('__', static function (string $string) {
				$translation = __($string, 'twist');

				if (func_num_args() > 1) {
					$arguments    = func_get_args();
					$arguments[0] = $translation;
					$translation  = sprintf(...$arguments);
				}

				return $translation;
			}, ['is_safe' => ['html']]),
			new TwigFunction('_x', static function (string $string, string $context) {
				$translation = _x($string, $context, 'twist');

				if (func_num_args() > 2) {
					$arguments    = func_get_args();
					$arguments[0] = $translation;
					array_splice($arguments, 1, 1);
					$translation = sprintf(...$arguments);
				}

				return $translation;
			}, ['is_safe' => ['html']]),
			new TwigFunction('_n', static function (string $single, string $plural, $number) {
				$translation = _n($single, $plural, $number, 'twist');

				return sprintf($translation, $number);
			}, ['is_safe' => ['html']]),
			new TwigFunction('_nx', static function (string $single, string $plural, $number, string $context) {
				$translation = _nx($single, $plural, $number, $context, 'twist');

				return sprintf($translation, $number);
			}, ['is_safe' => ['html']]),
		];

		if (Twist::config('view.debug') && class_exists(Kint::class)) {
			$functions[] = new TwigFunction('kint', static function ($debug) {
				Kint::dump($debug);
			}, ['is_safe' => ['all']]);
		}

		return $functions;
	}

}
