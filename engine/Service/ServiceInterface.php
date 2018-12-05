<?php

namespace Twist\Service;

/**
 * Interface ServiceInterface
 *
 * @package Twist\Service
 */
interface ServiceInterface
{

	/**
	 * @return bool
	 */
	public function boot(): bool;

	/**
	 *
	 */
	public function enable(): void;

	/**
	 *
	 */
	public function disable(): void;

}