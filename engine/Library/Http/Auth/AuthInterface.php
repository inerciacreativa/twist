<?php

namespace Twist\Library\Http\Auth;

use Twist\Library\Http\Request;

interface AuthInterface
{

	/**
	 * @return bool
	 */
	public function isReady(): bool;

	/**
	 * @param Request $request
	 *
	 * @return Request
	 */
	public function authorize(Request $request): Request;

	/**
	 * @return bool
	 */
	public function regenerate(): bool;

}