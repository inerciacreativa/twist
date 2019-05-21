<?php

namespace Twist\Service\Core\ImageSearch;

use RuntimeException;
use Twist\Library\Api\Client\VimeoClient;
use Twist\Library\Support\Arr;

/**
 * Class VimeoModule
 *
 * @package Twist\Service\Core\ImageSearch
 */
class VimeoModule extends VideoModule
{

	/**
	 * @inheritdoc
	 *
	 * @see https://stackoverflow.com/questions/5316973/simple-regular-expression-for-vimeo-videos
	 */
	protected function getRegexp(): string
	{
		return '@
        (?:https?://)?
        (?:player\.)?
        vimeo\.com/
        (?:video/|moogaloop\.swf\?clip_id=)?
        ([0-9]+)
        @ix';
	}

	/**
	 * @inheritdoc
	 */
	protected function getImage(string $id, int $width): ?array
	{
		try {
			$video = (new VimeoClient())->getVideo($id);
		} catch (RuntimeException $exception) {
			return null;
		}

		if ($video === null || !isset($video->pictures->sizes)) {
			return null;
		}

		$sizes   = $video->pictures->sizes;
		$urls    = Arr::pluck($sizes, 'link', 'width');
		$heights = Arr::pluck($sizes, 'height', 'width');
		$width   = $this->getClosestValue(array_keys($heights), $width);

		return [
			'src'    => $urls[$width],
			'alt'    => $video->name,
			'width'  => $width,
			'height' => (int) $heights[$width],
		];
	}

}