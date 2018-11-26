<?php

namespace Twist\View;

use Twist\Library\Util\Data;
use Twist\Library\Util\Macro;
use Twist\Service\Service;

class Context extends Service
{

	use Macro;

	private $local = [];

	private $shared = [];

	/**
	 * @inheritdoc
	 */
	public function boot(): void
	{
		foreach ((array) $this->config->get('data.view', []) as $name => $value) {
			$this->set($name, $value);
		}
	}

	protected function init(): void
	{
		foreach ((array) $this->config->get('data.global', []) as $name => $value) {
			$this->share($name, $value);
		}
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function share(string $key, $value): self
	{
		if (array_key_exists($key, $this->shared)) {
			throw new \InvalidArgumentException("The shared value '$key' was already defined.'");
		}

		$this->shared[$key] = $this->resolve($value);

		self::macro($key, function () use ($key) {
			return $this->shared[$key];
		});

		return $this;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function set(string $key, $value): self
	{
		$this->local[$key] = $value;

		return $this;
	}

	/**
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	public function get(string $key)
	{
		if ($this->has($key)) {
			return $this->local[$key];
		}

		return null;
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has(string $key): bool
	{
		return array_key_exists($key, $this->local);
	}

	/**
	 * @param string $key
	 *
	 * @return Context
	 */
	public function forget(string $key): self
	{
		unset($this->local[$key]);

		return $this;
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
	 */
	public function local(array $context): array
	{
		return array_map([
			$this,
			'resolve',
		], array_merge($this->local, $context));
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	protected function resolve($value)
	{
		if (\is_string($value) && class_exists($value)) {
			return new $value();
		}

		return Data::value($value);
	}

}