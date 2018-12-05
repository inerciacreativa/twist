<?php

namespace Twist\App;

use Pimple\Container;
use Twist\Service\ServiceInterface;
use Twist\Service\ServiceProviderInterface;

/**
 * Class Application
 *
 * @package Twist\App
 */
class App extends Container
{

	public const BOOT = 'after_setup_theme';

	public const INIT = 'init';

	public const REQUEST = 'parse_request';

	public const QUERY = 'parse_query';

	public const SETUP = 'wp';

	public const HEAD = 'wp_head';

	public const FOOT = 'wp_footer';

	/**
	 * @var array
	 */
	private $boot = [];

	/**
	 * @param string   $id
	 * @param callable $service
	 * @param bool     $boot
	 *
	 * @return $this
	 *
	 * @throws \RuntimeException
	 */
	public function service($id, $service, $boot = false): self
	{
		$this->offsetSet($id, $service);

		if ($boot) {
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
	public function boot(): void
	{
		foreach ($this->boot as $service) {
			if (($this[$service] instanceof ServiceInterface) && ($this[$service]->boot() === false)) {
				$this[$service]->disable();
				unset($this[$service]);
			}
		}
	}

}