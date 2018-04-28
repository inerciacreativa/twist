<?php

namespace Twist\Service;

/**
 * Interface ServiceInterface
 *
 * @package Twist\Service
 */
interface ServiceInterface
{

	public function boot(): void;

	public function start(): void;

	public function stop(): void;

}