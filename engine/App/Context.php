<?php

namespace Twist\App;

use Twist\Library\Util\Data;
use Twist\Service\Service;

/**
 * Class Context
 *
 * @package Twist\App
 */
class Context extends Service
{

	/**
	 * @var array
	 */
	private $view = [];

	/**
	 * @var array
	 */
	private $shared = [];

	/**
	 * @var AppException[]
	 */
	private $errors = [];

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
	 * @throws AppException
	 */
	protected function init(): void
	{
		$this->add((array) $this->config->get('context.view', []));

		foreach ((array) $this->config->get('context.shared', []) as $name => $value) {
			$this->share($name, $value);
		}
	}

	/**
	 * @param string $message
	 *
	 * @return $this
	 *
	 * @throws AppException
	 */
	public function error(string $message): self
	{
		$this->errors[] = new AppException($message, $this->config->get('app.debug', true));

		return $this;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 *
	 * @throws AppException
	 */
	public function share(string $key, $value): self
	{
		if ($this->is_shared($key)) {
			return $this->error("The key '$key' is already shared.");
		}

		if ($this->is_view($key)) {
			return $this->error("The key '$key' is already defined in the context.");
		}

		$this->shared[$key] = $value;

		return $this;
	}

	/**
	 * @param array $context
	 * @param bool  $overwrite
	 *
	 * @return Context
	 *
	 * @throws AppException
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
	 * @return $this
	 *
	 * @throws AppException
	 */
	public function set(string $key, $value, bool $overwrite = false): self
	{
		if ($this->is_shared($key)) {
			return $this->error("The key '$key' is already shared.");
		}

		if ($overwrite || !$this->is_view($key)) {
			$this->view[$key] = $value;
		}

		return $this;
	}

	/**
	 * @param string $key
	 *
	 * @return $this
	 */
	public function forget(string $key): self
	{
		if ($this->is_view($key)) {
			unset($this->view[$key]);
		}

		return $this;
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has(string $key): bool
	{
		return $this->is_view($key) || $this->is_shared($key);
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function is_view(string $key): bool
	{
		return array_key_exists($key, $this->view);
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function is_shared(string $key): bool
	{
		return array_key_exists($key, $this->shared);
	}

	/**
	 * @return AppException[]
	 */
	public function errors(): array
	{
		return $this->errors;
	}

	/**
	 * @return array
	 */
	public function shared(): array
	{
		return array_map([
			$this,
			'value',
		], $this->shared);
	}

	/**
	 * @param array $context
	 *
	 * @return array
	 *
	 * @throws AppException
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
		if (is_string($value) && class_exists($value)) {
			return new $value();
		}

		return Data::value($value);
	}

}