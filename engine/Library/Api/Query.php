<?php

namespace Twist\Library\Api;

use WP_Error;

/**
 * Class Query
 *
 * @package Twist\Library\Api
 */
class Query
{

	public const USER_AGENT = 'ic HTTP/2.0';

	public const READY = 0;

	public const ERROR = -1;

	public const SUCCESS = 200;

	/**
	 * @var string
	 */
	protected $endpoint;

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var array
	 */
	protected $parameters = [];

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var int
	 */
	protected $status = self::READY;

	/**
	 * @var array
	 */
	protected $request = [
		'user-agent' => self::USER_AGENT,
		'sslverify'  => false,
		'headers'    => [
			'Accept-Encoding' => 'gzip',
		],
		'cookies'    => [],
		'body'       => null,
	];

	/**
	 * @var array|WP_Error
	 */
	protected $response;

	/**
	 * Query constructor.
	 *
	 * @param string $endpoint
	 */
	public function __construct(string $endpoint)
	{
		$this->endpoint = $endpoint;
	}

	/**
	 * @param string $endpoint
	 *
	 * @return static
	 */
	public static function create(string $endpoint)
	{
		return new static($endpoint);
	}

	/**
	 * @param string $method
	 * @param string $path
	 * @param array  $parameters
	 * @param bool   $execute
	 *
	 * @return bool|$this
	 */
	public function query(string $method, string $path = '', array $parameters = [], bool $execute = true)
	{
		$this->path       = $path;
		$this->parameters = array_merge($this->parameters, $parameters);

		$this->request['method'] = strtoupper($method);

		return $execute ? $this->execute() : $this;
	}

	/**
	 * @return bool
	 */
	public function execute(): bool
	{
		$this->response = wp_remote_request($this->getUrl(), $this->request);

		if (is_wp_error($this->response)) {
			$this->status = self::ERROR;
		} else {
			$this->status = (int) wp_remote_retrieve_response_code($this->response);

			if ($this->status === self::SUCCESS) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $path
	 * @param array  $parameters
	 * @param bool   $execute
	 *
	 * @return bool|$this
	 */
	public function get(string $path = '', array $parameters = [], bool $execute = true)
	{
		return $this->query('GET', $path, $parameters, $execute);
	}

	/**
	 * @param string $path
	 * @param array  $parameters
	 * @param bool   $execute
	 *
	 * @return bool|Query
	 */
	public function post(string $path = '', array $parameters = [], bool $execute = true)
	{
		return $this->query('POST', $path, $parameters, $execute);
	}

	/**
	 * @return string
	 */
	public function getResponse(): ?string
	{
		if (is_array($this->response) && !is_wp_error($this->response)) {
			return wp_remote_retrieve_body($this->response);
		}

		return null;
	}

	/**
	 * @return int
	 */
	public function getStatus(): int
	{
		return $this->status;
	}

	/**
	 * @return string|null
	 */
	public function getError(): ?string
	{
		if (is_wp_error($this->response)) {
			return $this->response->get_error_message();
		}

		return null;
	}

	/**
	 * @return string
	 */
	public function getUrl(): string
	{
		if (empty($this->url)) {
			$this->url = empty($this->path) ? $this->endpoint : sprintf('%s/%s', rtrim($this->endpoint, '/'), ltrim($this->path, '/'));

			if (!empty($this->parameters)) {
				$this->url .= '?' . http_build_query($this->parameters);
			}
		}

		return $this->url;
	}

	/**
	 * @return string
	 */
	public function getId(): string
	{
		if (empty($this->id)) {
			$this->id = md5($this->getUrl());
		}

		return $this->id;
	}

	/**
	 * @param string $name
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setParameter(string $name, string $value): self
	{
		if (isset($this->parameters[$name])) {
			return $this;
		}

		$this->parameters[$name] = $value;

		return $this->reset(true);
	}

	/**
	 * @param string $name
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setHeader(string $name, string $value): self
	{
		$this->request['headers'][$name] = $value;

		return $this->reset();
	}

	/**
	 * @param string $name
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setCookie(string $name, string $value): self
	{
		$this->request['cookies'][$name] = $value;

		return $this->reset();
	}

	/**
	 * @param mixed $body
	 *
	 * @return $this
	 */
	public function setBody($body): self
	{
		$this->request['body'] = $body;

		return $this->reset();
	}

	/**
	 * @param bool $url
	 *
	 * @return $this
	 */
	protected function reset(bool $url = false): self
	{
		$this->response = null;
		$this->status   = self::READY;

		if ($url) {
			$this->url = null;
			$this->id  = null;
		}

		return $this;
	}

}