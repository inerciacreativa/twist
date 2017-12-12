<?php

namespace Twist\Service;

/**
 * Interface ServiceInterface
 *
 * @package Twist\Service
 */
interface ServiceInterface
{

	public function boot();

	public function start();

	public function stop();

}