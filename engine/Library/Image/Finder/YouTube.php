<?php

namespace Twist\Library\Image\Finder;

use ic\Framework\Api\Client\YouTubeClient;
use Twist\Library\Util\Arr;

/**
 * Class YouTube
 *
 * @package Twist\Library\Image\Finder
 */
class YouTube extends Finder
{

	/**
	 * @var YouTubeClient
	 */
	protected $api;

	/**
	 * @inheritdoc
	 */
	protected function getRegex(): string
	{
		// http://stackoverflow.com/questions/5830387/php-regex-find-all-youtube-video-ids-in-string
		return '@
        (?:https?://)?
        (?:[0-9A-Z-]+\.)?
        (?:youtu\.be/|youtube(?:-nocookie)?\.com\S*?[^\w\s-])
        ([\w\-]{11})
        (?=[^\w-]|$)
        [?=&+%\w.-]*
        @ix';
	}

	/**
	 * @inheritdoc
	 */
	protected function getImage(string $id): array
	{
		$data = $this->getApi()->getVideo($id);

		if (!\is_object($data) || !isset($data->items[0]->snippet)) {
			return [];
		}

		$video      = $data->items[0]->snippet;
		$thumbnails = (array) $video->thumbnails;
		$urls       = Arr::pluck($thumbnails, 'url', 'width');
		$heights    = Arr::pluck($thumbnails, 'height', 'width');
		$width      = static::closest(array_keys($heights), $this->width);

		return [
			'src'    => $urls[$width],
			'alt'    => $video->title,
			'width'  => $width,
			'height' => (int) $heights[$width],
		];
	}

	/**
	 * @return YouTubeClient
	 */
	protected function getApi(): YouTubeClient
	{
		if ($this->api === null) {
			$this->api = new YouTubeClient();
		}

		return $this->api;
	}

}