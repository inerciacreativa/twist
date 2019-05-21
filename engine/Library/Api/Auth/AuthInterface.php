<?php

namespace Twist\Library\Api\Auth;

use Twist\Library\Api\Query;

/**
 * Interface OAuthInterface
 *
 * @package Twist\Library\Api\Auth
 */
interface AuthInterface
{

	/**
	 * @return string
	 */
	public function getId(): string;

	/**
	 * @return bool
	 */
	public function isReady(): bool;

	/**
	 * @param Query $query
	 *
	 * @return Query
	 */
	public function authorize(Query $query): Query;

	/**
	 * @return bool
	 */
	public function regenerate(): bool;

}