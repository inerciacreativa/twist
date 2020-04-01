<?php

namespace Twist\Library\Support;

use InvalidArgumentException;

/**
 * Class Url
 *
 * @package Twist\Library\Support
 *
 * @property string $scheme
 * @property string $host
 * @property int    $port
 * @property string $user
 * @property string $pass
 * @property string $path
 * @property array  $query
 * @property string $fragment
 */
class Url
{

	/**
	 * @var string
	 */
	static protected $home;

	/**
	 * Allowed schemes.
	 *
	 * @var array
	 */
	static protected $schemes = [
		'http'   => '://',
		'https'  => '://',
		'ftp'    => '://',
		'ftps'   => '://',
		'sftp'   => '://',
		'ssh'    => '://',
		'telnet' => '://',
		'mailto' => ':',
	];

	/**
	 * @var array
	 */
	static protected $defaults = [
		'scheme'   => '',
		'host'     => '',
		'port'     => 0,
		'user'     => '',
		'pass'     => '',
		'path'     => '',
		'query'    => [],
		'fragment' => '',
	];

	/**
	 * @var array
	 */
	protected $components;

	/**
	 * @param string $url
	 *
	 * @return static
	 */
	public static function parse(string $url): Url
	{
		return new static($url);
	}

	/**
	 * @return Url
	 */
	public static function home(): Url
	{
		if (self::$home === null) {
			self::$home = self::parse(home_url());
		}

		return self::$home;
	}

	/**
	 * Url constructor.
	 *
	 * @param string $url
	 */
	public function __construct(string $url)
	{
		$url   = trim($url);
		$unset = [];

		if (strpos($url, '//') === 0) {
			$unset = ['scheme'];
			$url   = 'placeholder:' . $url;
		} else if (strpos($url, '/') === 0) {
			$unset = ['scheme', 'host'];
			$url   = 'placeholder://placeholder' . $url;
		}

		$components = @parse_url($url);

		if (false === $components) {
			$components = [];
		} else {
			// Remove the placeholder values.
			foreach ($unset as $key) {
				unset($components[$key]);
			}

			if (array_key_exists('path', $components)) {
				if (strpos($components['path'], '/') !== 0) {
					$components['path'] = '/' . $components['path'];
				}

				// Check for non US-ASCII chars in the path
				// It may fail to parse, so
				// encode each part of the path if necessary
				if (!preg_match('/^[[:graph:]]+$/', $url)) {
					$components['path'] = static::encodePath($components, $url);
				}
			}

			if (array_key_exists('query', $components)) {
				parse_str($components['query'], $query);
				$components['query'] = $query;
			}
		}

		if (isset($components['port'])) {
			$components['port'] = (int) $components['port'];
		}

		$this->components = array_merge(static::$defaults, $components);
	}

	/**
	 * @param array  $components
	 * @param string $url
	 *
	 * @return string
	 */
	protected static function encodePath(array $components, string $url): string
	{
		if (array_key_exists('fragment', $components)) {
			$url = str_replace('#' . $components['fragment'], '', $url);
		}

		if (array_key_exists('query', $components)) {
			$url = str_replace('?' . $components['query'], '', $url);
		}

		$path   = explode('/', trim($components['path'], '/'));
		$source = explode('/', trim($url, '/'));

		return array_reduce(array_reverse($path), static function ($result, $part) use (&$source) {
			$slug = array_pop($source);
			if ($part !== $slug) {
				$part = urlencode($slug);
			}

			return "/$part$result";
		}, '');
	}

	/**
	 * @param string $component
	 * @param mixed  $value
	 *
	 * @throws InvalidArgumentException
	 */
	public function __set(string $component, $value)
	{
		if (!array_key_exists($component, $this->components)) {
			throw new InvalidArgumentException('Component name not valid');
		}

		if ($component === 'path' && strpos($value, '/') !== 0) {
			$value = '/' . $value;
		} else if ($component === 'query') {
			if (empty($value)) {
				$value = [];
			} else if (is_array($value)) {
				$value = array_replace_recursive($this->components['query'], $value);
			} else {
				throw new InvalidArgumentException('The query must be an array');
			}
		} else if ($component === 'port') {
			$value = (int) $value;
		}

		$this->components[$component] = $value;
	}

	/**
	 * @param string $component
	 *
	 * @return mixed
	 *
	 * @throws InvalidArgumentException
	 */
	public function __get(string $component)
	{
		if (!array_key_exists($component, $this->components)) {
			throw new InvalidArgumentException('Component name not valid');
		}

		return $this->components[$component];
	}

	/**
	 * @param string $component
	 *
	 * @return bool
	 */
	public function __isset(string $component): bool
	{
		return array_key_exists($component, $this->components);
	}

	/**
	 * @param string $component
	 */
	public function __unset(string $component)
	{
		if (array_key_exists($component, $this->components)) {
			$default = '';

			if ($component === 'query') {
				$default = [];
			} else if ($component === 'port') {
				$default = 0;
			}

			$this->components[$component] = $default;
		}
	}

	/**
	 * @return string
	 */
	public function getDomain(): string
	{
		$domain = '';

		if (!empty($this->components['scheme'])) {
			$domain .= $this->components['scheme'] . static::$schemes[$this->components['scheme']];
		}

		if (!empty($this->components['user'])) {
			$domain .= $this->components['user'];
			if (isset($this->components['pass'])) {
				$domain .= ':' . $this->components['pass'];
			}
			$domain .= '@';
		}

		if (!empty($this->components['host'])) {
			$domain .= $this->components['host'];
		}

		if (!empty($this->components['port'])) {
			$domain .= ':' . $this->components['port'];
		}

		return $domain;
	}

	/**
	 * @return string
	 */
	public function getPath(): string
	{
		if (!empty($this->components['path'])) {
			return $this->components['path'];
		}

		return '/';
	}

	/**
	 * @return string
	 */
	public function getRoute(): string
	{
		$route = $this->getPath();

		if (!empty($this->components['query'])) {
			$route .= '?' . http_build_query($this->components['query']);
		}

		if (!empty($this->components['fragment'])) {
			$route .= '#' . $this->components['fragment'];
		}

		return $route;
	}

	/**
	 * @return bool
	 */
	public function isValid(): bool
	{
		if ($this->isAbsolute()) {
			return filter_var($this->render(), FILTER_VALIDATE_URL, []);
		}

		return $this->getDomain() === '';
	}

	/**
	 * @return bool
	 */
	public function isAbsolute(): bool
	{
		return !empty($this->components['host']);
	}

	/**
	 * @return bool
	 */
	public function isRelative(): bool
	{
		return empty($this->components['host']);
	}

	/**
	 * @return bool
	 */
	public function isLocal(): bool
	{
		if ($this->host === 'localhost' || $this->isRelative()) {
			return true;
		}

		return $this->host === self::home()->host;
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		return $this->getDomain() . $this->getRoute();
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->render();
	}

}
