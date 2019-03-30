<?php

namespace Twist\Service;

use InvalidArgumentException;
use RuntimeException;
use Twist\App\App;

/**
 * Interface ServiceProviderInterface
 *
 * @package Twist\Service
 */
interface ServiceProviderInterface
{

	/**
	 * Registers services on the given application.
	 *
	 * This method should only be used to configure services and parameters.
	 * It should not get services.
	 *
	 * @param App $app An Application instance
	 *
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function register(App $app): void;

}