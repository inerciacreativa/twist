<?php

namespace Twist\View\Twig;

use Kint\Kint;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twist\Asset;
use Twist\Library\Html\Attributes;
use Twist\Library\Html\Classes;
use Twist\Library\Html\Tag;
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
	 * @return TwigFilter[]
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
	 * @return TwigFunction[]
	 */
	public function getFunctions(): array
	{
		return array_filter(array_merge($this->getUrlFunctions(), $this->getAssetFunctions(), $this->getTranslationFunctions(), $this->getDebugFunctions()));
	}

	/**
	 * @return TwigFunction[]
	 */
	protected function getTranslationFunctions(): array
	{
		return [
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
	}

	/**
	 * @return TwigFunction[]
	 */
	protected function getDebugFunctions(): array
	{
		$functions = [];

		if (class_exists(Kint::class) && Twist::config('view.debug')) {
			$functions[] = new TwigFunction('kint', static function ($debug) {
				Kint::dump($debug);
			}, ['is_safe' => ['all']]);
		}

		return $functions;
	}

	/**
	 * @return TwigFunction[]
	 */
	protected function getUrlFunctions(): array
	{
		return [
			new TwigFunction('url_site', static function (string $path = '/', string $scheme = null) {
				return site_url($path, $scheme);
			}, ['is_safe' => ['all']]),
			new TwigFunction('url_home', static function (string $path = '/') {
				return home_url($path);
			}, ['is_safe' => ['all']]),
			new TwigFunction('url_admin', static function (string $path = '/') {
				return admin_url($path);
			}, ['is_safe' => ['all']]),
			new TwigFunction('url_request', static function () {
				global $wp;

				return home_url(trailingslashit($wp->request));
			}, ['is_safe' => ['all']]),
			new TwigFunction('url_search_query', static function (bool $escape = true) {
				return get_search_query($escape);
			}, ['is_safe' => ['all']]),
			new TwigFunction('url_asset', static function (string $filename, bool $parent = false) {
				return Asset::url($filename, $parent);
			}, ['is_safe' => ['all']]),
		];
	}

	/**
	 * @return TwigFunction[]
	 */
	protected function getAssetFunctions(): array
	{
		return [
			new TwigFunction('asset_image', [
				$this,
				'getAssetImage',
			], ['is_safe' => ['html']]),
			new TwigFunction('asset_svg_inline', [
				$this,
				'getAssetSvgInline',
			], ['is_safe' => ['html']]),
			new TwigFunction('asset_svg_icon', [
				$this,
				'getAssetSvgIcon',
			], ['is_safe' => ['html']]),
		];
	}

	/**
	 * @param string $filename
	 * @param array  $attributes
	 * @param bool   $parent
	 *
	 * @return string
	 */
	protected function getAssetImage(string $filename, array $attributes = [], bool $parent = false): string
	{
		$image = Tag::img(['src' => Asset::url($filename, $parent)]);
		$image->attributes(array_merge([
			'alt' => '',
		], $attributes));

		return $image->render();
	}

	/**
	 * @param string $path
	 * @param array  $attributes
	 *
	 * @return string
	 */
	protected function getAssetSvgInline(string $path, array $attributes = []): string
	{
		$file  = Asset::path($path);
		$image = @file_get_contents($file);

		if (!$image) {
			return '';
		}

		if ($svg = Tag::parse($image)) {
			$svg->attributes()->remove('version');
			$svg->attributes()->remove('xmlns');
			$svg->attributes()->remove('xmlns:xlink');
			$svg->attributes($attributes);
			$image = $svg->render();
		}

		return $image;
	}

	/**
	 * @param string      $icon
	 * @param string|null $title
	 *
	 * @return string
	 */
	protected function getAssetSvgIcon(string $icon, string $title = null): string
	{
		$class = "icon-$icon";
		$svg   = Tag::svg(['class' => "icon $class"]);

		$svg['focusable'] = 'false';

		if ($title) {
			$id = $this->getAssetSvgIconTitle($class, $title);

			$svg['role']            = 'img';
			$svg['aria-labelledby'] = $id;

			$svg->content(Tag::title(['id' => $id], $title));
		} else {
			$svg['aria-hidden'] = 'true';
		}

		return $svg->content(Tag::use([
			'href'        => "#icon-$icon",
			'aria-hidden' => 'true',
		]))->render(true);
	}

	/**
	 * @param string $class
	 * @param string $title
	 *
	 * @return string
	 */
	protected function getAssetSvgIconTitle(string $class, string $title): string
	{
		static $titles = [];

		$id = "${class}__title";

		$count = array_reduce($titles, static function (int $count, array $item) use ($id) {
			if (strpos($item['id'], $id) === 0) {
				return ++$count;
			}

			return $count;
		}, 0);

		$id       .= "-$count";
		$titles[] = ['id' => $id, 'title' => $title];

		return $id;
	}

}
