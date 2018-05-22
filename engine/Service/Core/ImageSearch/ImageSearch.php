<?php

namespace Twist\Service\Core\ImageSearch;

use Twist\Library\Util\Url;

/**
 * Class ImageSearch
 *
 * @package Twist\Service\Core\ImageSearch
 */
class ImageSearch implements ImageSearchInterface
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
	 * @var array
	 */
	protected $images = [];

	/**
	 * @inheritdoc
	 */
	public function search(string $html, int $width = 720): bool
	{
		self::$forbidenSources[] = Url::parse(home_url())->host;

		$dom = new \DOMDocument();
		@$dom->loadHTML($html);

		/** @var $image \DOMElement */
		foreach ($dom->getElementsByTagName('img') as $image) {
			if (!$image->hasAttribute('src')) {
				continue;
			}

			$source = Url::parse($image->getAttribute('src'));

			if (empty($source->host) || \in_array($source->host, static::$forbidenSources, true)) {
				continue;
			}

			$this->images[] = [
				'src'    => $source->get(),
				'id'     => $this->id($image),
				'alt'    => $this->attribute($image, 'alt', ''),
				'width'  => $this->attribute($image, 'width', 0),
				'height' => $this->attribute($image, 'height', 0),
			];
		}

		return !empty($this->images);
	}

	/**
	 * @inheritdoc
	 */
	public function get(): ?ExternalImage
	{
		if (empty($this->images)) {
			return null;
		}

		if (\count($this->images) > 1) {
			uasort($this->images, function ($a, $b) {
				$a = ($a['width'] * 10) + $a['height'];
				$b = ($b['width'] * 10) + $b['height'];

				if ($a === $b) {
					return 0;
				}

				return ($a > $b) ? -1 : 1;
			});
		}

		return new ExternalImage($this->images[0]);
	}

	/**
	 * @param \DOMElement $image
	 *
	 * @return int
	 */
	protected function id(\DOMElement $image): int
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
	protected function attribute(\DOMElement $image, string $attribute, $default)
	{
		$result = $default;

		if ($image->hasAttribute($attribute)) {
			$result = $image->getAttribute($attribute);
			settype($result, \gettype($default));
		}

		return $result;
	}

}