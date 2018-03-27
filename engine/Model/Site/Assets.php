<?php

namespace Twist\Model\Site;

use Twist\Library\Util\Tag;
use function Twist\asset_path;
use function Twist\asset_url;

class Assets
{

	protected $site;

	/**
	 * Asset constructor.
	 *
	 * @param \Twist\Model\Site\Site $site
	 */
	public function __construct(Site $site)
	{
		$this->site = $site;
	}

	/**
	 * @param string $filename
	 * @param bool   $parent
	 *
	 * @return string
	 */
	public function url(string $filename, bool $parent = false): string
	{
		return asset_url($filename, $parent);
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
			$logo = Tag::img(['src' => asset_url($filename)]);
		}

		$logo->attributes($attributes);
		$logo['alt'] = $this->site->name();

		return apply_filters('ic_twist_assets_logo', $logo);
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
		$image = Tag::img(['src' => asset_url($filename, $parent)]);
		$image->attributes($attributes);

		if (!isset($image['alt'])) {
			$image['alt'] = '';
		}

		return apply_filters('ic_twist_assets_image', $image);
	}

	/**
	 * @param string $path
	 * @param bool   $source
	 *
	 * @return string
	 */
	public function svg_inline(string $path, $source = false): string
	{
		$image = asset_path($path, false, $source);

		return file_get_contents($image);
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