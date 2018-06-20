<?php

namespace Twist\Library\Http;

use Twist\Library\Util\Arr;

/**
 * Class Request
 *
 * @package Twist\Library\Http
 */
class Request
{

	use HeadersTrait;

	/**
	 * @var Uri
	 */
	private $uri;

	/**
	 * @var string
	 */
	private $method;

	/**
	 * @var null|string|array
	 */
	private $body;

	/**
	 * @var string
	 */
	private $version;

	/**
	 * Request constructor.
	 *
	 * @param string              $method
	 * @param string|UriInterface $uri
	 * @param array               $headers
	 * @param null|string|array   $body
	 * @param string              $version
	 */
	public function __construct(string $method, $uri, array $headers = [], $body = null, string $version = '1.1')
	{
		$this->method  = strtoupper($method);
		$this->uri     = ($uri instanceof UriInterface) ? $uri : new Uri($uri);
		$this->body    = $body ?? null;
		$this->version = $version;

		$this->setHeaders($headers);

		if (!$this->hasHeader('Host')) {
			$this->updateHostFromUri();
		}
	}

	/**
	 * @param Request $request
	 * @param array   $changes
	 *
	 * @return Request
	 */
	public static function modify(Request $request, array $changes): Request
	{
		if (!$changes) {
			return $request;
		}

		$headers = $request->getHeaders();

		if (!isset($changes['uri'])) {
			$uri = $request->getUri();
		} else {
			// Remove the host header if one is on the URI
			if ($host = $changes['uri']->getHost()) {
				$changes['set_headers']['Host'] = $host;
				if ($port = $changes['uri']->getPort()) {
					$standardPorts = ['http' => 80, 'https' => 443];
					$scheme        = $changes['uri']->getScheme();
					if (isset($standardPorts[$scheme]) && $port !== $standardPorts[$scheme]) {
						$changes['set_headers']['Host'] .= ':' . $port;
					}
				}
			}
			$uri = $changes['uri'];
		}

		if (!empty($changes['remove_headers'])) {
			$headers = Arr::remove($headers, $changes['remove_headers']);
		}

		if (!empty($changes['set_headers'])) {
			$headers = Arr::remove($headers, array_keys($changes['set_headers']));
			$headers = $changes['set_headers'] + $headers;
		}

		if (isset($changes['query'])) {
			$uri = $uri->withQuery($changes['query']);
		}

		return new static($changes['method'] ?? $request->getMethod(), $uri, $headers, $changes['body'] ?? $request->getBody(), $changes['version'] ?? $request->getVersion());
	}

	/**
	 * @return Uri
	 */
	public function getUri(): Uri
	{
		return $this->uri;
	}

	/**
	 * @return string
	 */
	public function getMethod(): string
	{
		return $this->method;
	}

	/**
	 * @return null|string|array
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * @return string
	 */
	public function getVersion(): string
	{
		return $this->version;
	}

	/**
	 * @param UriInterface $uri
	 * @param bool         $preserveHost
	 *
	 * @return $this|Request
	 */
	public function withUri(UriInterface $uri, bool $preserveHost = false): Request
	{
		if ($uri === $this->uri) {
			return $this;
		}

		$new      = clone $this;
		$new->uri = $uri;

		if (!$preserveHost) {
			$new->updateHostFromUri();
		}

		return $new;
	}

	/**
	 * @param string $method
	 *
	 * @return Request
	 */
	public function withMethod(string $method): Request
	{
		$new         = clone $this;
		$new->method = strtoupper($method);

		return $new;
	}

	/**
	 * @param string $version
	 *
	 * @return Request
	 */
	public function withVersion(string $version): Request
	{
		if ($this->version === $version) {
			return $this;
		}

		$new          = clone $this;
		$new->version = $version;

		return $new;
	}

	/**
	 * @param string      $name
	 * @param string|null $value
	 *
	 * @return Request
	 */
	public function withQuery(string $name, string $value = null): Request
	{
		$new      = clone $this;
		$new->uri = Uri::withQueryValue($this->uri, $name, $value);

		return $new;
	}

	/**
	 * @param string $name
	 *
	 * @return Request
	 */
	public function withoutQuery(string $name): Request
	{
		$new      = clone $this;
		$new->uri = Uri::withoutQueryValue($this->uri, $name);

		return $new;
	}

	/**
	 * @param string       $header
	 * @param string|array $value
	 *
	 * @return Request
	 */
	public function withHeader(string $header, $value): Request
	{
		if (!\is_array($value)) {
			$value = [$value];
		}

		$value      = $this->trimHeaderValues($value);
		$normalized = strtolower($header);
		$new        = clone $this;

		if (isset($new->headerNames[$normalized])) {
			unset($new->headers[$new->headerNames[$normalized]]);
		}

		$new->headerNames[$normalized] = $header;
		$new->headers[$header]         = $value;

		return $new;
	}

	/**
	 * @param string       $header
	 * @param string|array $value
	 *
	 * @return Request
	 */
	public function withAddedHeader(string $header, $value): Request
	{
		if (!\is_array($value)) {
			$value = [$value];
		}

		$value      = $this->trimHeaderValues($value);
		$normalized = strtolower($header);
		$new        = clone $this;

		if (isset($new->headerNames[$normalized])) {
			$header                = $this->headerNames[$normalized];
			$new->headers[$header] = array_merge($this->headers[$header], $value);
		} else {
			$new->headerNames[$normalized] = $header;
			$new->headers[$header]         = $value;
		}

		return $new;
	}

	/**
	 * @param string $header
	 *
	 * @return Request
	 */
	public function withoutHeader(string $header): Request
	{
		$normalized = strtolower($header);
		if (!isset($this->headerNames[$normalized])) {
			return $this;
		}
		$header = $this->headerNames[$normalized];
		$new    = clone $this;
		unset($new->headers[$header], $new->headerNames[$normalized]);

		return $new;
	}

	/**
	 * @param null|string|array $body
	 * @param bool              $json
	 *
	 * @return $this
	 */
	public function withBody($body, bool $json = false): Request
	{
		$new = clone $this;

		if ($json) {
			if (\is_array($body)) {
				$body = json_encode($body);
			}

			$new = $new->withHeader('Accept', 'application/json')
			           ->withHeader('Content-Type', 'application/json');
		}

		$new->body = $body;

		return $new;
	}

	private function updateHostFromUri(): void
	{
		$host = $this->uri->getHost();

		if ($host === '') {
			return;
		}

		if (($port = $this->uri->getPort()) !== null) {
			$host .= ':' . $port;
		}

		if (isset($this->headerNames['host'])) {
			$header = $this->headerNames['host'];
		} else {
			$header                    = 'Host';
			$this->headerNames['host'] = 'Host';
		}

		// Ensure Host is the first header.
		// See: http://tools.ietf.org/html/rfc7230#section-5.4
		$this->headers = [$header => [$host]] + $this->headers;
	}
}