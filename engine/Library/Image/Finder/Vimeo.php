<?php

namespace Twist\Library\Image\Finder;

use ic\Framework\Api\Client\VimeoClient;
use Twist\Library\Util\Arr;

/**
 * Class Vimeo
 *
 * @package Twist\Library\Image\Finder
 */
class Vimeo extends Finder
{

	/**
	 * @var VimeoClient
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
        (?:player\.)?
        vimeo\.com/
        (?:video/|moogaloop\.swf\?clip_id=)?
        ([0-9]+)
        @ix';
	}

	/**
	 * @inheritdoc
	 */
	protected function getImage(string $id): array
	{
		$data = $this->getApi()->getVideo($id);

		if (!\is_object($data)) {
			return [];
		}

		$video   = $data;
		$urls    = Arr::pluck($video->pictures->sizes, 'link', 'width');
		$heights = Arr::pluck($video->pictures->sizes, 'height', 'width');
		$width   = static::closest(array_keys($heights), $this->width);

		return [
			'src'    => $urls[$width],
			'alt'    => $video->name,
			'width'  => $width,
			'height' => (int) $heights[$width],
		];
	}

	/**
	 * @return VimeoClient
	 */
	protected function getApi(): VimeoClient
	{
		if ($this->api === null) {
			$this->api = new VimeoClient();
		}

		return $this->api;
	}

}