<?php

namespace Twist\Service;

use Twist\App\App;
use Twist\Service\Core\ContentCleanerService;
use Twist\Service\Core\HeadCleanerService;
use Twist\Service\Core\LazyLoadService;
use Twist\Service\Core\RelativeUrlService;
use Twist\Service\Core\ThumbnailGeneratorService;

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
		$app->service(RelativeUrlService::id(), function (App $app) {
			return new RelativeUrlService($app);
		});

		$app->service(HeadCleanerService::id(), function (App $app) {
			return new HeadCleanerService($app);
		});

		$app->service(ContentCleanerService::id(), function (App $app) {
			return new ContentCleanerService($app);
		});

		$app->service(ThumbnailGeneratorService::id(), function (App $app) {
			return new ThumbnailGeneratorService($app);
		});

		$app->service(LazyLoadService::id(), function (App $app) {
			return new LazyLoadService($app, $app['theme'], $app['asset']);
		});
	}

}