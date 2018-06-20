<?php

namespace Twist\Library\Http\Auth;

use Twist\Library\Http\Request;

/**
 * Class OAuthKey
 *
 * @package Twist\Library\Http\Auth
 */
class OAuthKey implements AuthInterface
{

	/**
	 * @var array
	 */
	private $queries;

	/**
	 * @var array
	 */
	private $headers;

	/**
	 * OAuthKey constructor.
	 *
	 * @param array $queries
	 * @param array $headers
	 */
	public function __construct(array $queries, array $headers = [])
	{
		$this->queries = $queries;
		$this->headers = $headers;
	}

	/**
	 * @inheritdoc
	 */
	public function isReady(): bool
	{
		return true;
	}

	/**
	 * @param Request $request
	 *
	 * @return Request
	 */
	public function authorize(Request $request): Request
	{
		foreach ($this->queries as $name => $value) {
			$request = $request->withQuery($name, $value);
		}

		foreach ($this->headers as $name => $value) {
			$request = $request->withHeader($name, $value);
		}

		return $request;
	}

	/**
	 * @inheritdoc
	 */
	public function regenerate(): bool
	{
		return false;
	}

}