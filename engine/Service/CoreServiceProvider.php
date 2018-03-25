<?php

namespace Twist\Service;

use Twist\App\ServiceProviderInterface;
use Twist\App\App;
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
class CoreServiceProvider implements ServiceProviderInterface
{

	/**
	 * @inheritdoc
	 */
	public function register(App $app)
	{
		$app->service(RestService::id(), function (App $app) {
			return new RestService($app);
		}, true);

		$app->service(OEmbedService::id(), function (App $app) {
			return new OEmbedService($app);
		}, true);

		$app->service(RelativeUrlService::id(), function (App $app) {
			return new RelativeUrlService($app);
		}, true);

		$app->service(EmojiService::id(), function (App $app) {
			return new EmojiService($app);
		}, true);

		$app->service(ContentCleanerService::id(), function (App $app) {
			return new ContentCleanerService($app);
		}, true);
	}

}