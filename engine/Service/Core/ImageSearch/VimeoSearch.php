<?php

namespace Twist\Service\Core\ImageSearch;

use ic\Framework\Api\Client\VimeoClient;
use Twist\Library\Util\Arr;

/**
 * Class VimeoSearch
 *
 * @package Twist\Service\Core\ImageSearch
 */
class VimeoSearch extends VideoSearch
{

	/**
	 * @inheritdoc
	 *
	 * @see https://stackoverflow.com/questions/5316973/simple-regular-expression-for-vimeo-videos
	 */
	protected function regex(): string
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
	protected function retrieve(string $id, int $width): array
	{
		$video = (new VimeoClient())->getVideo($id);

		if ($video === null || !isset($video->pictures->sizes)) {
			return [];
		}

		$sizes   = $video->pictures->sizes;
		$urls    = Arr::pluck($sizes, 'link', 'width');
		$heights = Arr::pluck($sizes, 'height', 'width');
		$width   = $this->closest(array_keys($heights), $width);

		return [
			'src'    => $urls[$width],
			'alt'    => $video->name,
			'width'  => $width,
			'height' => (int) $heights[$width],
		];
	}

}