<?php

namespace Twist\Service\Core\ImageSearch;

use Twist\Library\Dom\Element;
use Twist\Library\Support\Url;

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
	protected static $forbiddenSources = [
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

		/** @var $image Element */
		foreach ($resolver->document()->getElementsByTagName('img') as $image) {
			if (!$image->hasAttribute('src')) {
				continue;
			}

			$source = Url::parse($image->getAttribute('src'));

			if (empty($source->host) || in_array($source->host, static::$forbiddenSources, true)) {
				continue;
			}

			$found = true;
			$resolver->add([
				'id'     => $this->getId($image),
				'src'    => $source->render(),
				'alt'    => $image->getAttribute('alt'),
				'width'  => $image->getAttribute('width', 0),
				'height' => $image->getAttribute('height', 0),
			]);
		}

		return $found;
	}

	/**
	 * @param Element $image
	 *
	 * @return int
	 */
	protected function getId(Element $image): int
	{
		if (!$image->hasAttribute('class')) {
			return 0;
		}

		if (preg_match('/wp-image-([\d]*)/i', $image->getAttribute('class'), $id)) {
			return (int) $id[1];
		}

		return 0;
	}

}
