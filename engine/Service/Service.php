<?php

namespace Twist\Service;

use Twist\App\App;
use Twist\App\Config;
use Twist\Library\Hook\HookDecorator;
use Twist\Library\Util\Str;

/**
 * Class Service
 *
 * @package Twist\Service
 */
abstract class Service implements ServiceInterface
{

	use HookDecorator;

	/**
	 * @var App
	 */
	protected $app;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var bool
	 */
	private $started = false;

	/**
	 * @return string
	 */
	public static function id(): string
	{
		static $name;

		if ($name === null) {
			$name = Str::snake(basename(str_replace(['\\', 'Service'], ['/', ''], static::class)), '_');
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

		$this->hook()->on('wp', 'init');
	}

	/**
	 * @inheritdoc
	 */
	public function start(): void
	{
		if (!$this->started) {
			$this->hook()->enable();

			$this->started = true;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function stop(): void
	{
		if ($this->started) {
			$this->hook()->disable();

			$this->started = false;
		}
	}

	/**
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	protected function config(string $name = '', $default = null)
	{
		$name = $name ? ".$name" : '';

		return $this->config->get('app.service.' . static::id() . $name, $default);
	}

	/**
	 *
	 */
	protected function init(): void
	{
	}

}