<?php

namespace Twist\Service;

use Twist\App\Action;
use Twist\App\Config;
use Twist\Library\Hook\Hookable;
use Twist\Library\Support\Str;

/**
 * Class Service
 *
 * @package Twist\Service
 */
abstract class Service implements ServiceInterface
{

	use Hookable;

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
			$name = Str::snake(Str::replaceLast($name, 'Service', ''));
		}

		return $name;
	}

	/**
	 * Service constructor.
	 *
	 * @param Config $config
	 * @param string $init
	 */
	public function __construct(Config $config, string $init = Action::SETUP)
	{
		$this->config = $config;

		$this->hook()->before($init, 'init');
	}

	/**
	 * @inheritdoc
	 */
	public function enable(): void
	{
		if (!$this->enabled) {
			$this->hook()->enable();

			$this->enabled = true;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function disable(): void
	{
		if ($this->enabled) {
			$this->hook()->disable();

			$this->enabled = false;
		}
	}

	/**
	 * @inheritdoc
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
