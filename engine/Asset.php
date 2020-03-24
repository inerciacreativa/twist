<?php

namespace Twist;

use Twist\Asset\Fonts;
use Twist\Asset\Manifest;
use Twist\Asset\Queue;
use Twist\Asset\Resources;
use Twist\Library\Support\Url;

/**
 * Class Asset
 *
 * @package Twist
 */
class Asset
{

	/**
	 * Return the URL for the filename translated from the manifest.
	 *
	 * @param string $filename
	 * @param bool   $parent
	 *
	 * @return Url
	 *
	 * @see Manifest::url()
	 *
	 */
	public static function url(string $filename, bool $parent = false): Url
	{
		return Twist::service('asset_manifest')->url($filename, $parent);
	}

	/**
	 * Return the complete path for the filename translated from the manifest.
	 *
	 * @param string $filename
	 * @param bool   $parent
	 *
	 * @return string
	 *
	 * @see Manifest::path()
	 *
	 */
	public static function path(string $filename, bool $parent = false): string
	{
		return Twist::service('asset_manifest')->path($filename, $parent);
	}

	/**
	 * @param string $path
	 * @param string $filename
	 * @param bool   $parent
	 *
	 * @see Manifest::manifest()
	 */
	public static function manifest(string $path, string $filename, bool $parent = false): void
	{
		Twist::service('asset_manifest')->manifest($path, $filename, $parent);
	}

	/**
	 * @param array $styles
	 * @param bool  $parent
	 *
	 * @see Queue::styles()
	 *
	 */
	public static function styles(array $styles, bool $parent = false): void
	{
		Twist::service('asset_queue')->styles($styles, $parent);
	}

	/**
	 * @param array $scripts
	 * @param bool  $parent
	 *
	 * @see Queue::scripts()
	 *
	 */
	public static function scripts(array $scripts, bool $parent = false): void
	{
		Twist::service('asset_queue')->scripts($scripts, $parent);
	}

	/**
	 * @param string          $id
	 * @param string|callable $script
	 *
	 * @see Queue::inline()
	 *
	 */
	public static function inline(string $id, $script): void
	{
		Twist::service('asset_queue')->inline($id, $script);
	}

	/**
	 * @param array       $fonts
	 * @param bool|string $loader
	 *
	 * @see Fonts::add()
	 */
	public static function fonts(array $fonts, $loader = true): void
	{
		Twist::service('asset_fonts')->add($fonts, $loader);
	}

	/**
	 * @param string           $type
	 * @param array|string|Url $resource
	 *
	 * @see Resources::add()
	 */
	public static function resource(string $type, $resource): void
	{
		Twist::service('asset_resources')->add($type, $resource);
	}

}
