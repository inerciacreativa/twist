<?php

namespace Twist\Service\Core\ImageSearch;

use ic\Framework\Api\Api;
use Twist\Model\Site\Site;

/**
 * Class TedSearch
 *
 * @package Twist\Service\Core\ImageSearch
 */
class TedSearch extends VideoSearch
{

	/**
	 * @inheritdoc
	 */
	protected function regex(): string
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
	 * @throws \Exception
	 */
	protected function retrieve(string $id, int $width): array
	{
		$data = (new Api('Ted', 'http://www.ted.com/'))->get('services/v1/oembed.json', [
			'url'      => 'http://embed.ted.com/talks/' . $id,
			'maxwidth' => $width,
			'language' => explode('-', Site::language())[0],
		]);

		if (!\is_object($data)) {
			return [];
		}

		return [
			'src'    => $data->thumbnail_url,
			'alt'    => $data->title,
			'width'  => (int) $data->thumbnail_width,
			'height' => (int) $data->thumbnail_height,
		];
	}

}