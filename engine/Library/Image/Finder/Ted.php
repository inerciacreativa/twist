<?php

namespace Twist\Library\Image\Finder;

use ic\Framework\Api\Api;

/**
 * Class Ted
 *
 * @package Twist\Library\Image\Finder
 */
class Ted extends Finder
{

	/**
	 * @var Api
	 */
	protected $api;

	/**
	 * @var string
	 */
	protected $language;

	/**
	 * Ted constructor.
	 */
	public function __construct()
	{
		$this->language = explode('-', get_bloginfo('language'))[0];
	}

	/**
	 * @inheritdoc
	 */
	protected function getRegex(): string
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
	 * @throws \Exception
	 */
	protected function getImage(string $id): array
	{
		$id   = 'http://embed.ted.com/talks/' . $id;
		$data = $this->getApi()->get('services/v1/oembed.json', [
			'url'      => $id,
			'maxwidth' => $this->width,
			'language' => $this->language,
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

	/**
	 * @return Api
	 */
	protected function getApi(): Api
	{
		if ($this->api === null) {
			$this->api = new Api('Ted', 'http://www.ted.com/');
		}

		return $this->api;
	}

}