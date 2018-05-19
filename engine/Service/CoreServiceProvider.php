<?php

namespace Twist\Service;

use Twist\App\App;
use Twist\Service\Api\OEmbedService;
use Twist\Service\Filter\ContentCleanerService;
use Twist\Service\Filter\EmojiService;
use Twist\Service\Filter\RelativeUrlService;

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
	public function register(App $app): void
	{
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