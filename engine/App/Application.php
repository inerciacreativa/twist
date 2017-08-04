<?php

namespace Twist\App;

use Pimple\Container;

/**
 * Class Application
 *
 * @package Twist\App
 */
class Application extends Container
{

    /**
     * @param string          $id
     * @param string|callable $value
     *
     * @throws \RuntimeException
     */
    public function service($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * @param string $id    The unique identifier for the parameter
     * @param mixed  $value A callable to protect from being evaluated
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function parameter($id, $value)
    {
        if (is_object($value) && method_exists($value, '__invoke')) {
            $this->protect($value);
        }

        $this->offsetSet($id, $value);
    }

    /**
     * Registers a service provider.
     *
     * @param ServiceProviderInterface $provider A ServiceProviderInterface instance
     * @param array                    $values   An array of values that customizes the provider
     *
     * @return static
     * @throws \RuntimeException
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
     * @param array $services
     */
    public function boot(array $services)
    {
        foreach ($services as $service) {
            if (isset($this[$service]) && ($this[$service] instanceof ServiceInterface)) {
                $this[$service]->boot();
            } elseif (is_a($service, Service::class, true)) {
                $name = $service::register($this);
                $this[$name]->boot();
            }
        }
    }

}