<?php

namespace Twist\App;

/**
 * Interface ExceptionInterface
 *
 * @package Twist\App
 */
interface AppExceptionInterface extends \Throwable
{

	public function isError(): bool;

}