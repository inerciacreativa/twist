<?php

namespace Twist\Service;

use Twist\App\App;
use Twist\App\Config;
use Twist\Library\Hook\Hookable;
use Twist\Library\Util\Str;

/**
 * Class Service
 *
 * @package Twist\Service
 */
abstract class Service implements ServiceInterface
{

	use Hookable;

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
	private $enabled = true;

	/**
	 * @return string
	 */
	public static function id(): string
	{
		static $name;

		if ($name === null) {
			$name = basename(str_replace('\\', '/', static::class));
			$name = Str::snake(Str::replaceLast($name, 'Service', ''), '_');
		}

		return $name;
	}

	/**
	 * Service constructor.
	 *
	 * @param App    $app
	 * @param string $init
	 */
	public function __construct(App $app, string $init = App::SETUP)
	{
		$this->app    = $app;
		$this->config = $this->app['config'];

		$this->hook()
		     ->before($init, 'init');
	}

	/**
	 * @inheritdoc
	 */
	public function enable(): void
	{
		if (!$this->enabled) {
			$this->hook()
			     ->enable();

			$this->enabled = true;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function disable(): void
	{
		if ($this->enabled) {
			$this->hook()
			     ->disable();

			$this->enabled = false;
		}
	}

	/**
	 * @return bool
	 */
	abstract public function boot(): bool;

	/**
	 *
	 */
	abstract protected function init(): void;

	/**
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	protected function config(string $name = '', $default = false)
	{
		$name = $name ? ".$name" : '';

		return $this->config->get('service.' . static::id() . $name, $default);
	}

}