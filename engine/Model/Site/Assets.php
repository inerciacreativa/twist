<?php

namespace Twist\Model\Site;

use Twist\App\Asset;
use Twist\Library\Hook\Hook;
use Twist\Library\Html\Tag;
use Twist\Twist;

/**
 * Class Asset
 *
 * @package Twist\Model\Site
 */
class Assets
{

	/**
	 * @var Asset
	 */
	private $asset;

	/**
	 * Asset constructor.
	 */
	public function __construct()
	{
		$this->asset = Twist::asset();
	}

	/**
	 * @param string $filename
	 * @param bool   $parent
	 *
	 * @return string
	 */
	public function url(string $filename, bool $parent = false): string
	{
		return $this->asset->url($filename, $parent);
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
			$logo = Tag::img(['src' => $this->asset->url($filename)]);
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
		$image = Tag::img(['src' => $this->asset->url($filename, $parent)]);
		$image->attributes(array_merge([
			'alt' => '',
		], $attributes));

		return Hook::apply('twist_asset_image', $image);
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public function svg_inline(string $path): string
	{
		$image = $this->asset->path($path);

		return (string) @file_get_contents($image);
	}

	/**
	 * @param string $icon
	 * @param null   $title
	 *
	 * @return string
	 */
	public function svg_icon(string $icon, $title = null): string
	{
		$svg = Tag::svg(['class' => "icon icon-$icon"]);

		if ($title) {
			$svg->content(Tag::title($title));
		} else {
			$svg['aria-hidden'] = 'true';
		}

		return $svg->content(Tag::use(['xlink:href' => "#icon-$icon"]));
	}

}