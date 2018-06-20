<?php

namespace Twist\Library\Http;

/**
 * Class Middleware
 *
 * @package Twist\Library\Http
 */
class Middleware
{

	/**
	 * Middleware that throws exceptions for 4xx or 5xx responses when the
	 * "http_error" request option is set to true.
	 *
	 * @return callable Returns a function that accepts the next handler.
	 */
	/*
	public static function httpErrors(): callable
	{
		return function (callable $handler) {
			return function ($request, array $options) use ($handler) {
				if (empty($options['http_errors'])) {
					return $handler($request, $options);
				}

				$response = $handler($request, $options);
				$code = $response->getStatus();
				if ($code < 400) {
					return $response;
				}

				throw RequestException::create($request, $response);
			};
		};
	}
	*/

	/**
	 * Middleware that invokes a callback before and after sending a request.
	 *
	 * The provided listener cannot modify or alter the response. It simply
	 * "taps" into the chain to be notified before returning the promise. The
	 * before listener accepts a request and options array, and the after
	 * listener accepts a request, options array, and response promise.
	 *
	 * @param callable $before Function to invoke before forwarding the request.
	 * @param callable $after  Function invoked after forwarding.
	 *
	 * @return callable Returns a function that accepts the next handler.
	 */
	public static function tap(callable $before = null, callable $after = null): callable
	{
		return function (callable $handler) use ($before, $after) {
			return function ($request, array $options) use ($handler, $before, $after) {
				if ($before) {
					$before($request, $options);
				}

				$response = $handler($request, $options);

				if ($after) {
					$after($request, $options, $response);
				}

				return $response;
			};
		};
	}

}