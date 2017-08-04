<?php

namespace Twist\View\Twig;

use Twist\App\Application;
use Twist\App\ServiceProviderInterface;

/**
 * Class TwigProvider
 *
 * @package Twist\View\Twig
 */
class TwigProvider implements ServiceProviderInterface
{
    /**
     * @param Application $app
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function register(Application $app)
    {
        $app->service('twig.service', function (Application $app) {
            $options = [
                'debug' => $app['config']->get('app.debug'),
                'cache' => $app['config']->get('view.cache'),
            ];

            return new TwigService($app['twig.loader'], $options);
        });

        $app->extend('twig.service', function (\Twig_Environment $provider) use ($app) {
            $provider->addExtension(new TwigExtension());
            $provider->addExtension(new \Twig_Extension_StringLoader());

            if ($app['config']->get('app.debug')) {
                $provider->addExtension(new \Twig_Extension_Debug());
            }

            foreach ((array)$app['config']->get('view.data') as $name => $class) {
                $provider->addGlobal($name, new $class());
            }

            return $provider;
        });

        $app->service('twig.loader', function (Application $app) {
            return new \Twig_Loader_Filesystem($app['config']->get('view.paths'));
        });
    }

}