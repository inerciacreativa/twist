<?php

namespace Twist\Service;

use Twist\App\App;
use Twist\App\Config;
use Twist\Library\Util\Str;

/**
 * Class Service
 *
 * @package Twist\Service
 */
abstract class Service implements ServiceInterface
{

	/**
	 * @var App
	 */
	protected $app;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @return string
	 */
	public static function id(): string
	{
		static $name;

		if ($name === null) {
			$name = Str::snake(basename(str_replace('\\', '/', static::class)), '.');
		}

		return $name;
	}

	/**
	 * Service constructor.
	 *
	 * @param App $app
	 */
	public function __construct(App $app)
	{
		$this->app    = $app;
		$this->config = $this->app['config'];

		$this->boot();
	}

	/**
	 * @inheritdoc
	 */
	public function boot(): void
	{
	}

	/**
	 * @inheritdoc
	 */
	public function start(): void
	{
	}

	/**
	 * @inheritdoc
	 */
	public function stop(): void
	{
	}

}