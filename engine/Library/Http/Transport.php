<?php

namespace Twist\Library\Http;

/**
 * Class Transport
 *
 * @package Twist\Library\Http
 */
class Transport
{

	/**
	 * @param Request $request
	 * @param array   $options
	 *
	 * @return Response
	 */
	public function __invoke(Request $request, array $options): Response
	{
		$parameters = array_merge($options, [
			'method'      => $request->getMethod(),
			'headers'     => $request->getAllHeaders(),
			'body'        => $request->getBody(),
			'httpversion' => $request->getVersion(),
		]);

		$response = wp_remote_request($request->getUri(), $parameters);

		return (new ResponseFactory())->create($response);
	}

}