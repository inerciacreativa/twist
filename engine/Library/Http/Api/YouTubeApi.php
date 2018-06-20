<?php

namespace Twist\Library\Http\Api;

use Twist\Library\Http\Client;

class YouTubeApi
{

	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @var string
	 */
	private $key;

	public function __construct(string $key)
	{
		$this->key    = $key;
		$this->client = new Client();
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public function getUri(string $path = ''): string
	{
		return 'https://www.googleapis.com/youtube/v' . $this->getVersion() . '/' . $path;
	}

	/**
	 * @return string
	 */
	public function getVersion(): string
	{
		return '3';
	}

	public function getVideo(string $id): ?\stdClass
	{
		$options = [
			'query' => [
				'id'   => $id,
				'key'  => $this->key,
				'part' => 'snippet',
			],
		];

		$response = $this->client->get($this->getUri('videos'), $options);

		if ($response->getStatus() !== 200) {
			return null;
		}

		return $response->getBody();
	}

	public function getUser(string $id)
	{
		$options = [
			'query' => [
				'id' => $id,
				'key'         => $this->key,
				'part'        => 'snippet',
			],
		];

		$response = $this->client->get($this->getUri('channels'), $options);

		if ($response->getStatus() !== 200) {
			return null;
		}

		return $response->getBody();
	}

}