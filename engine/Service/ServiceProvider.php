<?php

namespace Twist\Service;

use Twist\App\Application;
use Twist\App\Config;
use Twist\Service\Api\RestService;
use Twist\Service\Api\OEmbedService;
use Twist\Service\Filter\ContentCleanerService;
use Twist\Service\Filter\RelativeUrlService;
use Twist\Service\Filter\EmojiService;

/**
 * Class ServiceProvider
 *
 * @package Twist\Service
 */
class ServiceProvider implements ServiceProviderInterface
{

	/**
	 * @inheritdoc
	 */
	public function register(Application $app)
	{
		$app->service('config', function () {
			return new Config();
		});

		$app->service('view', function (Application $app) {
			return $app[$app['config']->get('view.service')];
		});

		$app->service(RestService::id(), function (Application $app) {
			return new RestService($app);
		}, true);

		$app->service(OEmbedService::id(), function (Application $app) {
			return new OEmbedService($app);
		}, true);

		$app->service(RelativeUrlService::id(), function (Application $app) {
			return new RelativeUrlService($app);
		}, true);

		$app->service(EmojiService::id(), function (Application $app) {
			return new EmojiService($app);
		}, true);

		$app->service(ContentCleanerService::id(), function (Application $app) {
			return new ContentCleanerService($app);
		}, true);
	}

}