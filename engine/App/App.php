<?php

namespace Twist\App;

use Pimple\Container;
use Twist\Service\Service;

/**
 * Class Application
 *
 * @package Twist\App
 */
class App extends Container
{

	/**
	 * @var array
	 */
	private $boot = [];

	/**
	 * @param string   $id
	 * @param callable $service
	 * @param bool     $start
	 *
	 * @return $this
	 *
	 * @throws \RuntimeException
	 */
	public function service($id, $service, $start = false): self
	{
		$this->offsetSet($id, $service);

		if ($start) {
			$this->boot[] = $id;
		}

		return $this;
	}

	/**
	 * @param string $id    The unique identifier for the parameter
	 * @param mixed  $value A callable to protect from being evaluated
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 * @throws \Pimple\Exception\ExpectedInvokableException
	 * @throws \Pimple\Exception\FrozenServiceException
	 */
	public function parameter($id, $value): self
	{
		if (\is_object($value) && method_exists($value, '__invoke')) {
			$this->protect($value);
		}

		$this->offsetSet($id, $value);

		return $this;
	}

	/**
	 * Registers a service provider.
	 *
	 * @param ServiceProviderInterface $provider A ServiceProviderInterface
	 *                                           instance
	 * @param array                    $values   An array of values that
	 *                                           customizes the provider
	 *
	 * @return $this
	 *
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 * @throws \Pimple\Exception\FrozenServiceException
	 */
	public function provider(ServiceProviderInterface $provider, array $values = []): self
	{
		$provider->register($this);

		foreach ($values as $id => $value) {
			$this->offsetSet($id, $value);
		}

		return $this;
	}

	/**
	 * Start services.
	 */
	public function boot()
	{
		foreach ($this->boot as $service) {
			if ($this[$service] instanceof Service) {
				$this[$service]->start();
			} else if ($this[$service] instanceof \Closure) {
				$this[$service]();
			}
		}
	}

}