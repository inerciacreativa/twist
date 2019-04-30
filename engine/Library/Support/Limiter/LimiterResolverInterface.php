<?php

namespace Twist\Library\Support\Limiter;

/**
 * Interface LimiterResolverInterface
 *
 * @package Twist\Library\Support\Limiter
 */
interface LimiterResolverInterface
{

	/**
	 * @param string $string
	 *
	 * @return int
	 */
	public function count(string $string): int;

	/**
	 * @param string $string
	 * @param int    $number
	 *
	 * @return string
	 */
	public function limit(string $string, int $number): string;

}