<?php

namespace Twist\App;

use Twist\Library\Util\Data;
use Twist\Library\Util\Macroable;
use Twist\Service\Service;

/**
 * Class Context
 *
 * @package Twist\App
 */
class Context extends Service
{

	use Macroable;

	/**
	 * @var array
	 */
	private $view = [];

	/**
	 * @var array
	 */
	private $shared = [];

	/**
	 * @var array
	 */
	private $errors = [];

	/**
	 * @var bool
	 */
	private $throw = false;

	/**
	 * @inheritdoc
	 */
	public function boot(): bool
	{
		return true;
	}

	/**
	 * @inheritdoc
	 *
	 * @throws \Exception
	 */
	protected function init(): void
	{
		$this->throw = !$this->config->get('app.debug', false);

		$this->add((array) $this->config->get('context.view', []));

		foreach ((array) $this->config->get('context.shared', []) as $name => $value) {
			$this->share($name, $value);
		}
	}

	/**
	 * @param string $message
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	protected function error(string $message): bool
	{
		$this->errors[] = new AppException($message, $this->throw);

		return false;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function share(string $key, $value): bool
	{
		if ($this->has($key, true)) {
			return $this->error("The key '$key' is already shared.'");
		}

		if ($this->has($key)) {
			return $this->error("The key '$key' is already defined in the context.'");
		}

		$this->shared[$key] = $this->value($value);

		if (is_callable($this->shared[$key])) {
			self::macro($key, function () use ($key) {
				return $this->shared[$key];
			});
		}

		return true;
	}

	/**
	 * @param array $context
	 * @param bool  $overwrite
	 *
	 * @return Context
	 *
	 * @throws \Exception
	 */
	public function add(array $context, bool $overwrite = false): self
	{
		foreach ($context as $key => $value) {
			$this->set($key, $value, $overwrite);
		}

		return $this;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @param bool   $overwrite
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function set(string $key, $value, bool $overwrite = false): bool
	{
		if ($this->has($key, true)) {
			return $this->error("The key '$key' is already shared.'");
		}

		if ($overwrite || !$this->has($key)) {
			$this->view[$key] = $value;

			return true;
		}

		return false;
	}

	/**
	 * @param string $key
	 *
	 * @return mixed|bool
	 *
	 * @throws \Exception
	 */
	public function get(string $key)
	{
		if ($this->has($key)) {
			return $this->view[$key];
		}

		return $this->error("The key '$key' is not defined in the context.'");
	}

	/**
	 * @param string $key
	 * @param bool   $inShared
	 *
	 * @return bool
	 */
	public function has(string $key, bool $inShared = false): bool
	{
		if ($inShared) {
			return array_key_exists($key, $this->shared);
		}

		return array_key_exists($key, $this->view);
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function forget(string $key): bool
	{
		if ($this->has($key)) {
			unset($this->view[$key]);

			return true;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function shared(): array
	{
		return $this->shared;
	}

	/**
	 * @param array $context
	 *
	 * @return array
	 *
	 * @throws \Exception
	 */
	public function resolve(array $context): array
	{
		$this->add($context, true);

		return array_map([
			$this,
			'value',
		], $this->view);
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	protected function value($value)
	{
		if (\is_string($value) && class_exists($value)) {
			return new $value();
		}

		return Data::value($value);
	}

}