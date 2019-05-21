<?php

namespace Twist\Library\Api\Client;

use RuntimeException;
use Twist\Library\Api\Auth\AuthInterface;
use Twist\Library\Api\Auth\OAuthKey;
use Twist\Twist;

/**
 * Class YouTubeClient
 *
 * @package Twist\Library\Api\Client
 */
class YouTubeClient extends Client
{

	/**
	 * @inheritdoc
	 */
	public function getAuth(): ?AuthInterface
	{
		static $auth;
		if ($auth === null) {
			if (empty($this->credentials['key'])) {
				throw new RuntimeException('Could not find the credentials!');
			}

			$auth = new OAuthKey([
				'key'  => $this->credentials['key'],
				'part' => 'snippet',
			]);
		}

		return $auth;
	}

	/**
	 * @inheritdoc
	 */
	protected function getCredentials(): array
	{
		return Twist::config('credentials.youtube', []);
	}

	/**
	 * @inheritdoc
	 */
	public function getName(): string
	{
		return 'YouTube';
	}

	/**
	 * @inheritdoc
	 */
	public function getVersion(): string
	{
		return '3';
	}

	/**
	 * @inheritdoc
	 */
	public function getDomain(string $path = ''): string
	{
		return 'https://www.youtube.com' . $path;
	}

	/**
	 * @inheritdoc
	 */
	public function getEndpoint(): string
	{
		return 'https://www.googleapis.com/youtube/v' . $this->getVersion();
	}

	/**
	 * @inheritdoc
	 */
	public function getUrls(): array
	{
		static $urls;
		if ($urls === null) {
			$urls = [
				'video'          => $this->getDomain('/watch?v=#ID#'),
				'userVideos'     => $this->getDomain('/user/#ID#'),
				'channelVideos'  => $this->getDomain('/channel/#ID#'),
				'playlistVideos' => $this->getDomain('/playlist?list=#ID#'),
				'embed'          => $this->getDomain('/embed/#ID#?version=' . $this->getVersion()),
			];
		}

		return $urls;
	}

	/**
	 * @param string $videoId
	 *
	 * @return null|object
	 */
	public function getVideo(string $videoId): ?object
	{
		return $this->getApi()->get('videos', ['id' => $videoId]);
	}

	/**
	 * @param string $userId
	 *
	 * @return null|object
	 */
	public function getUser(string $userId): ?object
	{
		return $this->getApi()->get('channels', ['forUsername' => $userId]);
	}

	/**
	 * @param string $userId
	 * @param int    $maxResults
	 *
	 * @return null|object
	 */
	public function getUserVideos(string $userId, int $maxResults = 10): ?object
	{
		$channel = $this->getApi()->get('channels', [
			'forUsername' => $userId,
			'part'        => 'contentDetails',
		]);

		if (!$channel) {
			return null;
		}

		$playlistId = $channel->items[0]->contentDetails->relatedPlaylists->uploads;

		return $this->getPlaylistVideos($playlistId, $maxResults);
	}

	/**
	 * @param string $channelId
	 *
	 * @return null|object
	 */
	public function getChannel(string $channelId): ?object
	{
		return $this->getApi()->get('channels', ['id' => $channelId]);
	}

	/**
	 * @param string $channelId
	 * @param int    $maxResults
	 *
	 * @return null|object
	 */
	public function getChannelVideos(string $channelId, int $maxResults = 10): ?object
	{
		$channel = $this->getApi()->get('channels', [
			'id'   => $channelId,
			'part' => 'contentDetails',
		]);

		if (!$channel) {
			return null;
		}

		$playlistId = $channel->items[0]->contentDetails->relatedPlaylists->uploads;

		return $this->getPlaylistVideos($playlistId, $maxResults);
	}

	/**
	 * @param string $playlistId
	 *
	 * @return null|object
	 */
	public function getPlaylist(string $playlistId): ?object
	{
		return $this->getApi()->get('playlists', ['id' => $playlistId]);
	}

	/**
	 * @param string $playlistId
	 * @param int    $maxResults
	 *
	 * @return null|object
	 */
	public function getPlaylistVideos(string $playlistId, int $maxResults = 10): ?object
	{
		$playlist = $this->getApi()->get('playlistItems', [
			'playlistId' => $playlistId,
			'maxResults' => $maxResults,
		]);

		if (!$playlist) {
			return null;
		}

		$videos = [];
		foreach ($playlist->items as $id => $item) {
			$videos[] = $item->snippet->resourceId->videoId;
		}

		if (!empty($videos)) {
			$durations = $this->getApi()->get('videos', [
				'id'   => implode(',', $videos),
				'part' => 'contentDetails',
			]);

			if ($durations) {
				foreach ($playlist->items as $id => $item) {
					$playlist->items[$id]->snippet->duration = $durations->items[$id]->contentDetails->duration;
				}
			}
		}

		return $playlist;
	}

}