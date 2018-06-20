<?php

namespace Twist\Library\Http;

/**
 * Class ResponseFactory
 *
 * @package Twist\Library\Http
 */
class ResponseFactory
{

	/**
	 * @param $response
	 *
	 * @return Response
	 */
	public function create($response): Response
	{
		if (\is_array($response)) {
			return $this->createFromArray($response);
		}

		if ($response instanceof \WP_Error) {
			return $this->createFromError($response);
		}

		return new Response(500);
	}

	/**
	 * @param array $response
	 *
	 * @return Response
	 */
	protected function createFromArray(array $response): Response
	{
		if (!isset($response['response']['code'])) {
			return new Response(500);
		}

		$status  = (int) $response['response']['code'];
		$headers = $response['headers'] ?? [];
		$body    = $response['body'] ?? '';

		if ($headers instanceof \Requests_Utility_CaseInsensitiveDictionary) {
			$headers = $headers->getAll();
		}

		return new Response($status, $headers, $body);
	}

	/**
	 * @param \WP_Error $error
	 *
	 * @return Response
	 */
	protected function createFromError(\WP_Error $error): Response
	{
		return new Response(500, [], implode("\n", $error->get_error_messages()));
	}

}