<?php

namespace Twist\App;

use Twist\Library\Util\Str;

/**
 * Class Service
 *
 * @package Twist\App
 */
abstract class Service implements ServiceInterface
{

    /**
     * @var Application
     */
    protected $app;

    /**
     * @return string
     */
    public static function name(): string
    {
        static $name;

        if ($name === null) {
            $name = Str::snake(basename(str_replace('\\', '/', get_called_class())), '.');
        }

        return $name;
    }

    /**
     * @param Application $app
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public static function register(Application $app): string
    {
        $app->service(static::name(), function (Application $app) {
            return new static($app);
        });

        return static::name();
    }

    /**
     * Service constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param string|null $key
     * @param null        $default
     *
     * @return mixed|Config
     */
    public function config(string $key = null, $default = null)
    {
        /** @var Config $config */
        $config = $this->app['config'];

        if ($key === null) {
            return $config;
        }

        return $config->get($key, $default);

    }
}