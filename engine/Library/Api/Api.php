<?php

namespace Twist\Library\Api;

use Twist\Library\Api\Auth\AuthInterface;
use Twist\Library\Data\Cache;
use RuntimeException;

/**
 * Class Api
 *
 * @package Twist\Library\Api
 */
class Api
{

	public const CACHE = 3600;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $endpoint;

	/**
	 * @var AuthInterface
	 */
	protected $auth;

	/**
	 * @var bool
	 */
	protected $json = true;

	/**
	 * @var bool
	 */
	protected $cache;

	/**
	 * @var bool
	 */
	protected $exceptions = false;

	/**
	 * @var array
	 */
	protected $errors = [];

	/**
	 * @var Query
	 */
	protected $query;

	/**
	 * Api constructor.
	 *
	 * @param string             $name
	 * @param string             $endpoint
	 * @param AuthInterface|null $auth
	 */
	public function __construct(string $name, string $endpoint, AuthInterface $auth = null)
	{
		$this->name     = $name;
		$this->endpoint = $endpoint;
		$this->auth     = $auth;
		$this->cache    = static::CACHE;
	}

	/**
	 * @param string             $name
	 * @param string             $endpoint
	 * @param AuthInterface|null $auth
	 *
	 * @return static
	 */
	public static function create(string $name, string $endpoint, AuthInterface $auth = null): Api
	{
		return new static($name, $endpoint, $auth);
	}

	/**
	 * @param string $method
	 * @param array  $parameters
	 * @param bool   $cache
	 *
	 * @return null|string|object
	 * @throws RuntimeException
	 *
	 */
	public function get($method, array $parameters = [], bool $cache = true)
	{
		return $this->query('GET', $method, $parameters, $cache);
	}

	/**
	 * @param string $method
	 * @param array  $parameters
	 * @param bool   $cache
	 *
	 * @return null|string|object
	 * @throws RuntimeException
	 *
	 */
	public function post($method, array $parameters = [], bool $cache = true)
	{
		return $this->query('POST', $method, $parameters, $cache);
	}

	/**
	 * @param string $http
	 * @param string $method
	 * @param array  $parameters
	 * @param bool   $cache
	 *
	 * @return null|string|object
	 * @throws RuntimeException
	 *
	 */
	public function query(string $http, string $method, array $parameters = [], bool $cache = true)
	{
		$result = null;

		try {
			$query  = $this->prepare($http, $method, $parameters);
			$result = $this->execute($query, $cache);

			$this->query = $query;

			if ($this->json) {
				$result = json_decode($result, false);
			}
		} catch (RuntimeException $exception) {
			$this->errors[] = $exception->getMessage();

			if ($this->exceptions) {
				throw $exception;
			}
		}

		return $result;
	}

	/**
	 * @return Query
	 */
	public function getQuery(): Query
	{
		return $this->query;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param bool $json
	 *
	 * @return $this
	 */
	public function setJson(bool $json = true): self
	{
		$this->json = $json;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function getJson(): bool
	{
		return $this->json;
	}

	/**
	 * @param int $cache
	 *
	 * @return $this
	 */
	public function setCache(int $cache): self
	{
		$this->cache = $cache;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCache(): int
	{
		return $this->cache;
	}

	/**
	 * @param bool $exceptions
	 *
	 * @return $this
	 */
	public function setExceptions(bool $exceptions = true): self
	{
		$this->exceptions = $exceptions;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * @param string $http
	 * @param string $method
	 * @param array  $parameters
	 *
	 * @return Query
	 * @throws RuntimeException
	 *
	 */
	protected function prepare(string $http, string $method, array $parameters = []): Query
	{
		$query = Query::create($this->endpoint)
		              ->query($http, $method, $parameters, false);

		if ($this->auth) {
			if ($this->auth->isReady()) {
				$query = $this->auth->authorize($query);
			} else {
				throw new RuntimeException(sprintf("The OAuth module is not ready for authorization,\nID: %s", $this->auth->getId()));
			}
		}

		return $query;
	}

	/**
	 * @param Query $query
	 * @param bool  $cache
	 * @param bool  $retry
	 *
	 * @return string
	 * @throws RuntimeException
	 *
	 */
	protected function execute(Query $query, bool $cache, bool $retry = false): string
	{
		$id = $this->name . '_query_' . $query->getId();

		if ($cache && !$retry && ($result = Cache::get($id)) !== false) {
			return $result;
		}

		if ($query->execute()) {
			$result = $query->getResponse();

			if ($cache) {
				Cache::set($id, $result, $this->cache);
			}

			return $result;
		}

		if (!$retry && $this->auth && $this->auth->regenerate()) {
			$query = $this->auth->authorize($query);

			return $this->execute($query, $cache, true);
		}

		throw new RuntimeException(sprintf("The query produced an error.\nRequest: %s\nResponse: %s\nError: %s", $query->getUrl(), $query->getResponse(), $query->getError()));
	}

}