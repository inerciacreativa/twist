<?php

namespace Twist\Library\Http\Auth;

use Twist\Library\Data\Cache;
use Twist\Library\Http\Request;
use Twist\Library\Http\Uri;

/**
 * Class OAuthToken
 *
 * @package Twist\Library\Http\Auth
 */
class OAuthToken implements AuthInterface
{

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $token;

	/**
	 * @var string|Uri
	 */
	private $uri;

	/**
	 * @var array
	 */
	private $headers;

	/**
	 * @var string
	 */
	private $credentials;

	/**
	 * OAuthToken constructor.
	 *
	 * @param string|Uri $uri
	 * @param string     $id
	 * @param string     $secret
	 * @param array      $headers
	 */
	public function __construct($uri, string $id, string $secret, array $headers = [])
	{
		$this->uri         = $uri;
		$this->headers     = $headers;
		$this->credentials = base64_encode($id . ':' . $secret);

		$this->getToken();
	}

	/**
	 * @return bool
	 */
	public function isReady(): bool
	{
		return $this->token !== null;
	}

	/**
	 * @param Request $request
	 *
	 * @return Request
	 */
	public function authorize(Request $request): Request
	{
		if ($this->token) {
			$request = $request->withHeader('Authorization', 'Bearer ' . $this->token);

			foreach ($this->headers as $name => $value) {
				$request = $request->withHeader($name, $value);
			}
		}

		return $request;
	}

	/**
	 * @return bool
	 */
	public function regenerate(): bool
	{
		return $this->getNewToken();
	}

	/**
	 * @return string
	 */
	private function getId(): string
	{
		if ($this->id === null) {
			$this->id = '_oauth_' . md5($this->uri) . '_' . md5(serialize($this->headers));
		}

		return $this->id;
	}

	/**
	 *
	 */
	private function getToken(): void
	{
		$this->token = Cache::get($this->getId(), null);

		if (!$this->token && $this->getNewToken()) {
			Cache::set($this->getId(), $this->token);
		}
	}

	/**
	 * Generate the bearer token for unauthenticated requests following the OAuth 2.0 Client Credentials Grant.
	 *
	 * @return bool
	 */
	private function getNewToken(): bool
	{
		$this->token = null;

		$request = new Request('POST', $this->uri, [
			'Authorization' => 'Basic ' . $this->credentials,
			'Content-Type'  => 'application/x-www-form-urlencoded;charset=UTF-8',
		], ['grant_type' => 'client_credentials']);

		$response = $request->get();

		if ($response->getStatus() === 200) {
			$data = $response->getBody(true);

			if (isset($data->access_token)) {
				$this->token = $data->access_token;

				return true;
			}
		}

		return false;
	}

}