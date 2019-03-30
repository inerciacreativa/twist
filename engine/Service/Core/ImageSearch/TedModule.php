<?php

namespace Twist\Service\Core\ImageSearch;

use Exception;
use ic\Framework\Api\Api;
use Twist\Model\Site\Site;

/**
 * Class TedModule
 *
 * @package Twist\Service\Core\ImageSearch
 */
class TedModule extends VideoModule
{

	/**
	 * @inheritdoc
	 */
	protected function getRegexp(): string
	{
		return '@
        (?:https?://)?
        (?:www\.|embed\.)?
        ted\.com/
        (?:index\.php/)?
        talks/
        (?:lang/[A-Z]{2,3}/)?
        ([0-9A-Z_]+(\.html)?)
        @ix';
	}

	/**
	 * @inheritdoc
	 *
	 * @throws Exception
	 */
	protected function getImage(string $id, int $width): ?array
	{
		$data = (new Api('Ted', 'http://www.ted.com/'))->get('services/v1/oembed.json', [
			'url'      => 'http://embed.ted.com/talks/' . $id,
			'maxwidth' => $width,
			'language' => explode('-', Site::language())[0],
		]);

		if (!is_object($data)) {
			return null;
		}

		return [
			'src'    => $data->thumbnail_url,
			'alt'    => $data->title,
			'width'  => (int) $data->thumbnail_width,
			'height' => (int) $data->thumbnail_height,
		];
	}

}