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

	/**
	 * YouTubeApi constructor.
	 *
	 * @param string $key
	 */
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

	/**
	 * @param string $id
	 *
	 * @return null|\stdClass
	 */
	public function getVideo(string $id): ?\stdClass
	{
		$options = [
			'query' => [
				'key'  => $this->key,
				'id'   => $id,
				'part' => 'snippet',
			],
		];

		$response = $this->client->get($this->getUri('videos'), $options);

		if ($response->getStatus() !== 200) {
			return null;
		}

		return $response->getBody();
	}

	/**
	 * @param string $id
	 *
	 * @return null|\stdClass
	 */
	public function getChannel(string $id): ?\stdClass
	{
		$options = [
			'query' => [
				'key'  => $this->key,
				'id'   => $id,
				'part' => 'snippet',
			],
		];

		$response = $this->client->get($this->getUri('channels'), $options);

		if ($response->getStatus() !== 200) {
			return null;
		}

		return $response->getBody();
	}

	/**
	 * @param string $id
	 * @param int    $limit
	 *
	 * @return null|\stdClass
	 */
	public function getChannelVideos(string $id, int $limit = 10): ?\stdClass
	{
		$options = [
			'query' => [
				'key'  => $this->key,
				'id'   => $id,
				'part' => 'contentDetails',
			],
		];

		$response = $this->client->get($this->getUri('channels'), $options);

		if ($response->getStatus() !== 200) {
			return null;
		}

		$playlist = $response->getBody()->items[0]->contentDetails->relatedPlaylists->uploads;

		return $this->getPlaylistVideos($playlist, $limit);
	}

	/**
	 * @param string $id
	 *
	 * @return null|\stdClass
	 */
	public function getPlaylist(string $id): ?\stdClass
	{
		$options = [
			'query' => [
				'key'  => $this->key,
				'id'   => $id,
				'part' => 'snippet',
			],
		];

		$response = $this->client->get($this->getUri('playlists'), $options);

		if ($response->getStatus() !== 200) {
			return null;
		}

		return $response->getBody();
	}

	/**
	 * @param string $id
	 * @param int    $limit
	 *
	 * @return null|\stdClass
	 */
	public function getPlaylistVideos(string $id, int $limit = 10): ?\stdClass
	{
		$options = [
			'query' => [
				'key'        => $this->key,
				'playlistId' => $id,
				'maxResults' => $limit,
				'part'       => 'snippet',
			],
		];

		$response = $this->client->get($this->getUri('playlistItems'), $options);

		if ($response->getStatus() !== 200) {
			return null;
		}

		$playlist = $response->getBody();
		$videos   = [];

		foreach ($playlist->items as $item) {
			$videos[] = $item->snippet->resourceId->videoId;
		}

		if ($videos) {
			$options   = [
				'query' => [
					'key'  => $this->key,
					'id'   => implode(',', $videos),
					'part' => 'contentDetails',
				],
			];

			$info = $this->client->get($this->getUri('videos'), $options);

			if ($info->getStatus() === 200) {
				$videos = $info->getBody();
				foreach ($playlist->items as $code => $item) {
					$playlist->items[$code]->snippet->duration = $videos->items[$code]->contentDetails->duration;
				}
			}
		}

		return $playlist;
	}

}