<?php

namespace Twist\App;

use Pimple\Container;
use Twist\Service\ServiceProviderInterface;

/**
 * Class Application
 *
 * @package Twist\App
 */
class Application extends Container
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
	 * @throws \RuntimeException
	 */
	public function service($id, $service, $start = false)
	{
		$this->offsetSet($id, $service);

		if ($start) {
			$this->boot[] = $id;
		}
	}

	/**
	 * @param string $id    The unique identifier for the parameter
	 * @param mixed  $value A callable to protect from being evaluated
	 *
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 * @throws \Pimple\Exception\ExpectedInvokableException
	 * @throws \Pimple\Exception\FrozenServiceException
	 */
	public function parameter($id, $value)
	{
		if (\is_object($value) && method_exists($value, '__invoke')) {
			$this->protect($value);
		}

		$this->offsetSet($id, $value);
	}

	/**
	 * Registers a service provider.
	 *
	 * @param ServiceProviderInterface $provider A ServiceProviderInterface
	 *                                           instance
	 * @param array                    $values   An array of values that
	 *                                           customizes the provider
	 *
	 * @return static
	 *
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 * @throws \Pimple\Exception\FrozenServiceException
	 */
	public function provider(ServiceProviderInterface $provider, array $values = [])
	{
		$provider->register($this);

		foreach ($values as $id => $value) {
			$this->offsetSet($id, $value);
		}

		return $this;
	}

	/**
	 *
	 */
	public function boot()
	{
		foreach ($this->boot as $service) {
			$this[$service]->start();
		}
	}

}