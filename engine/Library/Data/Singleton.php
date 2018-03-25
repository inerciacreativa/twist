<?php

namespace Twist\Library\Data;

/**
 * Class Singleton
 *
 * @package Twist\Library\Data
 */
class Singleton
{

    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @throws \RuntimeException
     *
     * @return static
     */
    public static function getInstance()
    {
        return self::create();
    }

    /**
     * @throws \RuntimeException
     *
     * @return static
     */
    public static function create()
    {
        $class = \get_called_class();

        if (!isset(self::$instances[$class])) {
            if (\func_num_args()) {
                try {
                    $reflection  = new \ReflectionClass($class);
                    $instance    = $reflection->newInstanceWithoutConstructor();
                    $constructor = $reflection->getConstructor();
                    $constructor->setAccessible(true);
                    $constructor->invokeArgs($instance, \func_get_args());

                    self::$instances[$class] = $instance;
                } catch (\ReflectionException $exception) {
                    throw new \RuntimeException(sprintf('Could not create class "%s" with parameters.', $class));
                }
            } else {
                self::$instances[$class] = new static;
            }
        }

        return self::$instances[$class];
    }

    /**
     * Singleton constructor.
     */
    protected function __construct()
    {
    }

    /**
     *
     */
    final protected function __clone()
    {
    }

    /**
     * @throws \RuntimeException
     */
    final public function __wakeup()
    {
        throw new \RuntimeException('Cannot unserialize singleton.');
    }

}