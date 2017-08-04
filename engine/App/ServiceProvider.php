<?php

namespace Twist\App;

use Twist\Service\ContentService;
use Twist\Service\UrlService;
use Twist\Service\EmbedService;
use Twist\Service\EmojiService;
use Twist\Service\RestService;

/**
 * Class ServiceProvider
 *
 * @package Twist\App
 */
class ServiceProvider implements ServiceProviderInterface
{

    /**
     * @param Application $app
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function register(Application $app)
    {
        $app->service('config', function () {
            return new Config();
        });

        $app->service('view', function (Application $app) {
            return $app[$app['config']->get('view.service')];
        });

        $app->service('filter.url', function (Application $app) {
            return new UrlService($app);
        });

        $app->service('filter.emoji', function (Application $app) {
            return new EmojiService($app);
        });

        $app->service('filter.rest', function (Application $app) {
            return new RestService($app);
        });

        $app->service('filter.embed', function (Application $app) {
            return new EmbedService($app);
        });

        $app->service('filter.content', function (Application $app) {
            return new ContentService($app);
        });
    }

}