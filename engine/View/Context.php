<?php

namespace Twist\View;

use Twist\Library\Support\Data;
use Twist\Service\Service;

/**
 * Class Context
 *
 * @package Twist\View
 */
class Context extends Service
{

	/**
	 * @var array
	 */
	private $context = [];

	/**
	 * @inheritDoc
	 */
	public function boot(): bool
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function init(): void
	{
		$this->set((array) $this->config->get('view.context', []));
	}

	/**
	 * @param array $context
	 *
	 * @return $this
	 */
	public function set(array $context): self
	{
		foreach ($context as $key => $value) {
			$this->context[$key] = $value;
		}

		return $this;
	}

	/**
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	public function get(string $key)
	{
		return $this->has($key) ? $this->context[$key] : null;
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has(string $key): bool
	{
		return array_key_exists($key, $this->context);
	}

	/**
	 * @param string|array $keys
	 *
	 * @return $this
	 */
	public function forget($keys): self
	{
		foreach ((array) $keys as $key) {
			unset($this->context[$key]);
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function all(): array
	{
		return $this->resolve($this->context);
	}

	/**
	 * @param array $context
	 *
	 * @return array
	 */
	public function resolve(array $context): array
	{
		return array_map(static function ($value) {
			if (is_string($value) && class_exists($value)) {
				return new $value();
			}

			return Data::value($value);
		}, $context);
	}

}
