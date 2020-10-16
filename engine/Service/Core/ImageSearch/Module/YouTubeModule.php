<?php

namespace Twist\Service\Core\ImageSearch\Module;

use RuntimeException;
use Twist\Library\Api\Client\YouTubeClient;
use Twist\Library\Support\Arr;

/**
 * Class YouTubeModule
 *
 * @package Twist\Service\Core\ImageSearch
 */
class YouTubeModule extends VideoModule
{

	/**
	 * @inheritdoc
	 *
	 * @see http://stackoverflow.com/questions/5830387/php-regex-find-all-youtube-video-ids-in-string
	 */
	protected function getRegexp(): string
	{
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
	protected function getImage(string $id, int $width): ?array
	{
		try {
			$data = (new YouTubeClient())->getVideo($id);
		} catch (RuntimeException $exception) {
			return null;
		}

		if ($data === null || !isset($data->items[0]->snippet)) {
			return null;
		}

		$video      = $data->items[0]->snippet;
		$thumbnails = (array) $video->thumbnails;
		$urls       = Arr::pluck($thumbnails, 'url', 'width');
		$heights    = Arr::pluck($thumbnails, 'height', 'width');
		$width      = $this->getClosestValue(array_keys($heights), $width);

		return [
			'src'    => $urls[$width],
			'alt'    => $video->title,
			'width'  => $width,
			'height' => (int) $heights[$width],
		];
	}

}
