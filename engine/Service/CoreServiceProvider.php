<?php

namespace Twist\Service;

use Twist\App\App;
use Twist\Service\Core\ContentCleanerService;
use Twist\Service\Core\DisableEmojiService;
use Twist\Service\Core\RelativeUrlService;

/**
 * Class CoreServiceProvider
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
		$app->service(DisableEmojiService::id(), function (App $app) {
			return new DisableEmojiService($app);
		});

		$app->service(RelativeUrlService::id(), function (App $app) {
			return new RelativeUrlService($app);
		});

		$app->service(ContentCleanerService::id(), function (App $app) {
			return new ContentCleanerService($app);
		});
	}

}