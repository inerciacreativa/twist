<?php

namespace Twist\Library\Http;

use Twist\Library\Util\Arr;
use Twist\Library\Util\Json;

/**
 * Class Client
 *
 * @package Twist\Library\Http
 *
 * @method Response get($uri, $options = [])
 */
class Client
{

	public const USER_AGENT = 'ic HTTP/3.0';

	/**
	 * @var array
	 */
	private $config;

	/**
	 * Client constructor.
	 *
	 * @param array $config
	 */
	public function __construct(array $config = [])
	{
		if (!isset($config['transport'])) {
			$config['transport'] = new Transport();
		} else if (!\is_callable($config['transport'])) {
			throw new \InvalidArgumentException('transport must be a callable');
		}

		if (isset($config['uri'])) {
			$config['uri'] = ($config['uri'] instanceof UriInterface) ? $config['uri'] : new Uri($config['uri']);
		}

		$this->setConfig($config);
	}

	/**
	 * @param $method
	 * @param $arguments
	 *
	 * @return Response
	 */
	public function __call($method, $arguments): Response
	{
		if (\count($arguments) < 1) {
			throw new \InvalidArgumentException('Magic request methods require a URI and optional options array');
		}

		$uri     = $arguments[0];
		$options = $arguments[1] ?? [];

		return $this->request($method, $uri, $options);
	}

	/**
	 * @param string                   $method
	 * @param null|string|UriInterface $uri
	 * @param array                    $options
	 *
	 * @return Response
	 */
	public function request(string $method, $uri = '', array $options = []): Response
	{
		$options = $this->getOptions($options);
		$uri     = $this->getUri($uri, $options);
		$headers = $options['headers'] ?? [];
		$body    = $options['body'] ?? null;
		$version = $options['version'] ?? '1.1';

		$request = new Request($method, $uri, $headers, $body, $version);
		unset($options['headers'], $options['body'], $options['version']);

		return $this->transfer($request, $options);
	}

	/**
	 * @param Request $request
	 * @param array   $options
	 *
	 * @return Response
	 */
	public function send(Request $request, array $options = []): Response
	{
		$options = $this->getOptions($options);
		$uri     = $this->getUri($request->getUri(), $options);

		return $this->transfer($request->withUri($uri, $request->hasHeader('Host')), $options);
	}

	/**
	 * @param Request $request
	 * @param array   $options
	 *
	 * @return Response
	 */
	private function transfer(Request $request, array $options): Response
	{
		$request   = $this->setOptions($request, $options);
		$transport = $options['transport'];

		return $transport($request, $options);
	}

	/**
	 * @param array $config
	 */
	private function setConfig(array $config): void
	{
		$this->config = array_merge([
			'timeout'     => 4,
			'redirection' => 4,
			'sslverify'   => true,
			'user-agent'  => static::USER_AGENT,
		], $config);
	}

	/**
	 * @param Request $request
	 * @param array   $options
	 *
	 * @return Request
	 */
	private function setOptions(Request $request, array &$options): Request
	{
		$modify = [
			'set_headers' => [],
		];

		if (isset($options['headers'])) {
			$modify['set_headers'] = $options['headers'];
			unset($options['headers']);
		}

		if (isset($options['form_params'])) {
			$options['body'] = http_build_query($options['form_params'], '', '&');
			unset($options['form_params']);

			// Ensure that we don't have the header in different case and set the new value.
			$options['_conditional']                 = Arr::remove($options['_conditional'], ['Content-Type']);
			$options['_conditional']['Content-Type'] = 'application/x-www-form-urlencoded';
		}

		if (isset($options['json'])) {
			$options['body'] = Json::encode($options['json']);
			unset($options['json']);

			// Ensure that we don't have the header in different case and set the new value.
			$options['_conditional']                 = Arr::remove($options['_conditional'], ['Content-Type']);
			$options['_conditional']['Content-Type'] = 'application/json';
		}

		if (!empty($options['auth']) && \is_array($options['auth'])) {
			$value = $options['auth'];
			$type  = isset($value[2]) ? strtolower($value[2]) : 'basic';

			switch ($type) {
				case 'basic':
					// Ensure that we don't have the header in different case and set the new value.
					$modify['set_headers']                  = Arr::remove($modify['set_headers'], ['Authorization']);
					$modify['set_headers']['Authorization'] = 'Basic ' . base64_encode("$value[0]:$value[1]");
					break;
			}
		}

		if (isset($options['query'])) {
			$value = $options['query'];
			if (\is_array($value)) {
				$value = http_build_query($value, null, '&', PHP_QUERY_RFC3986);
			}

			if (!\is_string($value)) {
				throw new \InvalidArgumentException('query must be a string or array');
			}

			$modify['query'] = $value;
			unset($options['query']);
		}

		$request = Request::modify($request, $modify);

		if (isset($options['_conditional'])) {
			// Build up the changes so it's in a single clone of the message.
			$modify = [];
			foreach ($options['_conditional'] as $name => $value) {
				if (!$request->hasHeader($name)) {
					$modify['set_headers'][$name] = $value;
				}
			}

			$request = Request::modify($request, $modify);
			// Don't pass this internal value along to middleware/handlers.
			unset($options['_conditional']);
		}

		return $request;
	}

	/**
	 * @param array $options
	 *
	 * @return array
	 */
	private function getOptions(array $options): array
	{
		$defaults = $this->config;

		if (!empty($defaults['headers'])) {
			$defaults['_conditional'] = $defaults['headers'];
			unset($defaults['headers']);
		}

		if (array_key_exists('headers', $options)) {
			// Allows default headers to be unset.
			if ($options['headers'] === null) {
				$defaults['_conditional'] = null;
				unset($options['headers']);
			} else if (!\is_array($options['headers'])) {
				throw new \InvalidArgumentException('headers must be an array');
			}
		}

		// Shallow merge defaults underneath options.
		$result = $options + $defaults;

		// Remove null values.
		return Arr::remove($result);
	}

	/**
	 * @param null|string|UriInterface $uri
	 * @param array                    $options
	 *
	 * @return UriInterface
	 */
	private function getUri($uri, array $options): UriInterface
	{
		$uri = $uri ?? '';
		$uri = ($uri instanceof UriInterface) ? $uri : new Uri($uri);

		if (isset($options['uri'])) {
			$uri = UriResolver::resolve($options['uri'], $uri);
		}

		return $uri->getScheme() === '' && $uri->getHost() !== '' ? $uri->withScheme('http') : $uri;
	}

}
