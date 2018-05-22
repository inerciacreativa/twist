<?php

namespace Twist\Service\Core\ImageSearch;

use ic\Framework\Api\Client\YouTubeClient;
use Twist\Library\Util\Arr;

/**
 * Class YouTubeSearch
 *
 * @package Twist\Service\Core\ImageSearch
 */
class YouTubeSearch extends VideoSearch
{

	/**
	 * @inheritdoc
	 *
	 * @see http://stackoverflow.com/questions/5830387/php-regex-find-all-youtube-video-ids-in-string
	 */
	protected function regex(): string
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
	protected function retrieve(string $id, int $width): array
	{
		$data = (new YouTubeClient())->getVideo($id);

		if ($data === null || !isset($data->items[0]->snippet)) {
			return [];
		}

		$video      = $data->items[0]->snippet;
		$thumbnails = (array) $video->thumbnails;
		$urls       = Arr::pluck($thumbnails, 'url', 'width');
		$heights    = Arr::pluck($thumbnails, 'height', 'width');
		$width      = $this->closest(array_keys($heights), $width);

		return [
			'src'    => $urls[$width],
			'alt'    => $video->title,
			'width'  => $width,
			'height' => (int) $heights[$width],
		];
	}

}