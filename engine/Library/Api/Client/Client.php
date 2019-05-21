<?php

namespace Twist\Library\Api\Client;

use Twist\Library\Api\Api;
use Twist\Library\Api\Auth\AuthInterface;
use Twist\Library\Support\Arr;

/**
 * Class Client
 *
 * @package Twist\Library\Api\Client
 */
abstract class Client implements ClientInterface
{

	/**
	 * @var Api
	 */
	protected $api;

	/**
	 * @var array
	 */
	protected $credentials = [];

	/**
	 * @param array $credentials
	 *
	 * @return static
	 */
	public static function create(array $credentials = [])
	{
		return new static($credentials);
	}

	/**
	 * Client constructor.
	 *
	 * @param array $credentials
	 */
	public function __construct(array $credentials = [])
	{
		$this->credentials = Arr::defaults($this->getCredentials(), $credentials);
	}

	/**
	 * @inheritdoc
	 */
	public function setCache(int $cache)
	{
		$this->getApi()->setCache($cache);

		return $this;
	}

	/**
	 * @return Api
	 */
	public function getApi(): Api
	{
		if ($this->api === null) {
			$this->api = Api::create($this->getName(), $this->getEndpoint(), $this->getAuth());
		}

		return $this->api;
	}

	/**
	 * @inheritdoc
	 */
	public function getUrl(string $type, string $id): string
	{
		$urls = $this->getUrls();
		$url  = $urls[$type] ?? $type;

		return str_replace('#ID#', $id, $url);
	}

	/**
	 * @inheritdoc
	 */
	public function getAuth(): ?AuthInterface
	{
		return null;
	}

	/**
	 * @return array
	 */
	protected function getCredentials(): array
	{
		return [];
	}

}