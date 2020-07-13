<?php

namespace Twist\Model\Site;

use Kint\Kint;
use Twist\App\Action;
use Twist\Asset;
use Twist\Library\Hook\Hook;
use Twist\Library\Html\Tag;
use Twist\Model\Site\Assets\AssetsGroup;
use Twist\Model\Site\Assets\Links;
use Twist\Model\Site\Assets\Metas;
use Twist\Model\Site\Assets\Scripts;
use Twist\Model\Site\Assets\Styles;
use Twist\Model\Site\Assets\Title;

/**
 * Class Asset
 *
 * @package Twist\Model\Site
 */
class Assets
{

	/**
	 * @return AssetsGroup
	 */
	public static function head(): AssetsGroup
	{
		return new AssetsGroup(Action::HEAD, [
			Title::class,
			Metas::class,
			Links::class,
			Styles::class,
			Scripts::class,
		]);
	}

	/**
	 * @return AssetsGroup
	 */
	public static function foot(): AssetsGroup
	{
		return new AssetsGroup(Action::FOOT, [
			Scripts::class,
		]);
	}

	/**
	 * @param string $filename
	 * @param bool   $parent
	 *
	 * @return string
	 */
	public function url(string $filename, bool $parent = false): string
	{
		return Asset::url($filename, $parent);
	}

	/**
	 * @param string $filename
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public function logo(string $filename = null, array $attributes = []): string
	{
		if (empty($filename) && ($id = get_theme_mod('custom_logo'))) {
			$logo = Tag::parse(wp_get_attachment_image($id, 'full'));
		} else {
			$logo = Tag::img(['src' => Asset::url($filename)]);
		}

		$logo->attributes(array_merge([
			'alt' => Site::name(),
		], $attributes));

		return Hook::apply('twist_asset_logo', $logo);
	}

	/**
	 * @param string $filename
	 * @param array  $attributes
	 * @param bool   $parent
	 *
	 * @return string
	 */
	public function image(string $filename, array $attributes = [], bool $parent = false): string
	{
		$image = Tag::img(['src' => Asset::url($filename, $parent)]);
		$image->attributes(array_merge([
			'alt' => '',
		], $attributes));

		return Hook::apply('twist_asset_image', $image);
	}

	/**
	 * @param string $path
	 * @param array  $attributes
	 *
	 * @return string|null
	 */
	public function svg_inline(string $path, array $attributes = []): ?string
	{
		$file  = Asset::path($path);
		$image = @file_get_contents($file);

		if (!$image) {
			return null;
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
	 * @param string $icon
	 * @param null   $title
	 *
	 * @return string
	 */
	public function svg_icon(string $icon, $title = null): string
	{
		$class = "icon-$icon";
		$svg   = Tag::svg(['class' => "icon $class"]);

		$svg['focusable'] = 'false';

		if ($title) {
			$id = $this->getSvgTitle($class, $title);

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
	protected function getSvgTitle(string $class, string $title): string
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
