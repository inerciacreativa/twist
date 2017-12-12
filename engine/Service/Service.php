<?php

namespace Twist\Service;

use Twist\App\Application;
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
	 * @var Application
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
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app    = $app;
		$this->config = $this->app['config'];

		$this->boot();
	}

	/**
	 * @inheritdoc
	 */
	public function boot()
	{
	}

	/**
	 * @inheritdoc
	 */
	public function start()
	{
	}

	/**
	 * @inheritdoc
	 */
	public function stop()
	{
	}

}