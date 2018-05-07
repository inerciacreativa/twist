<?php

namespace Twist\Library\Image\Finder;

use Twist\Library\Image\ImageCollection;
use Twist\Library\Util\Url;

/**
 * Class Images
 *
 * @package Twist\Library\Image\Finder
 */
class Images implements FinderInterface
{

	/**
	 * @var array
	 */
	protected static $forbidenSources = [
		'feeds.feedburner.com',
		'blogger.googleusercontent.com',
		'feedads.g.doubleclick.net',
		'stats.wordpress.com',
		'feeds.wordpress.com',
	];

	/**
	 * @var Url
	 */
	protected $home;

	/**
	 * Images constructor.
	 */
	public function __construct()
	{
		$this->home = Url::parse(home_url());
	}

	/**
	 * @inheritdoc
	 */
	public function search(string $html, ImageCollection $collection = null, int $limit = 0, int $width = 720): ImageCollection
	{
		if ($collection === null) {
			$collection = new ImageCollection();
		}

		$dom = new \DOMDocument();
		@$dom->loadHTML($html);

		/** @var $image \DOMElement */
		foreach ($dom->getElementsByTagName('img') as $image) {
			if (!$image->hasAttribute('src')) {
				continue;
			}

			$source = Url::parse($image->getAttribute('src'));

			if (\in_array($source->host, static::$forbidenSources, false)) {
				continue;
			}

			$collection->append([
				'src'    => $source->get(),
				'id'     => $this->getId($image, $source),
				'alt'    => $this->getAttribute($image, 'alt', ''),
				'width'  => $this->getAttribute($image, 'width', 0),
				'height' => $this->getAttribute($image, 'height', 0),
			]);

			if ($limit > 0 && $collection->count() === $limit) {
				break;
			}
		}

		return $collection;
	}

	/**
	 * @param \DOMElement $image
	 * @param Url         $source
	 *
	 * @return int
	 */
	protected function getId(\DOMElement $image, Url $source): int
	{
		if (!$image->hasAttribute('class')) {
			return 0;
		}

		if (empty($source->host) || $source->host === $this->home->host) {
			if (preg_match('/wp-image-([\d]*)/i', $image->getAttribute('class'), $id)) {
				return (int) $id[1];
			}
		}

		return 0;
	}

	/**
	 * @param \DOMElement $image
	 * @param string      $attribute
	 * @param mixed       $default
	 *
	 * @return mixed
	 */
	protected function getAttribute(\DOMElement $image, string $attribute, $default)
	{
		$result = $default;

		if ($image->hasAttribute($attribute)) {
			$result = $image->getAttribute($attribute);
			settype($result, \gettype($default));
		}

		return $result;
	}

}