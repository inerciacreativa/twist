<?php

namespace Twist\Service\Core\ImageSearch;

use Twist\Library\Util\Url;

/**
 * Class ContentModule
 *
 * @package Twist\Service\Core\ImageSearch
 */
class ContentModule implements ModuleInterface
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
	 * @inheritdoc
	 */
	public function search(ImageResolver $resolver, bool $all = false): bool
	{
		$found = false;
		$dom   = new \DOMDocument();
		@$dom->loadHTML($resolver->content());

		/** @var $image \DOMElement */
		foreach ($dom->getElementsByTagName('img') as $image) {
			if (!$image->hasAttribute('src')) {
				continue;
			}

			$source = Url::parse($image->getAttribute('src'));

			if (empty($source->host) || \in_array($source->host, static::$forbidenSources, true)) {
				continue;
			}

			$found = true;
			$resolver->add([
				'id'     => $this->getId($image),
				'src'    => $source->get(),
				'alt'    => $this->getAttribute($image, 'alt', ''),
				'width'  => $this->getAttribute($image, 'width', 0),
				'height' => $this->getAttribute($image, 'height', 0),
			]);
		}

		return $found;
	}

	/**
	 * @param \DOMElement $image
	 *
	 * @return int
	 */
	protected function getId(\DOMElement $image): int
	{
		if (!$image->hasAttribute('class')) {
			return 0;
		}

		if (preg_match('/wp-image-([\d]*)/i', $image->getAttribute('class'), $id)) {
			return (int) $id[1];
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