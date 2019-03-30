<?php

namespace Twist\App;

use Throwable;

/**
 * Interface ExceptionInterface
 *
 * @package Twist\App
 */
interface AppExceptionInterface extends Throwable
{

	public function isError(): bool;

}