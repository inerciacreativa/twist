<?php

namespace Twist\Library\Hook;

/**
 * Class BoundedAction
 *
 * @package Twist\Library\Hook
 */
class BoundedAction extends Action
{

    /**
     * BoundedAction constructor.
     *
     * @param string $hook
     * @param mixed  $object
     * @param string $method
     * @param array  $parameters {
     *
     * @type int     $priority
     * @type int     $arguments
     * @type bool    $enabled
     * }
     */
    public function __construct($hook, $object, $method, array $parameters = [])
    {
        $callback = $this->getCallback($object, $method);

        parent::__construct($hook, $callback, $parameters);

        $this->setId(get_class($object), $method);
    }

    /**
     * Builds the callback.
     *
     * @param mixed  $object
     * @param string $method
     *
     * @return callable
     */
    protected function getCallback($object, $method): callable
    {
        return \Closure::bind(function () use ($method) {
            return call_user_func_array([$this, $method], func_get_args());
        }, $object, $object);
    }

}