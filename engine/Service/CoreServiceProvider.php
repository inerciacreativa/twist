<?php

namespace Twist\Service;

use Twist\App\App;
use Twist\Service\Core\ContentCleanerService;
use Twist\Service\Core\HeadCleanerService;
use Twist\Service\Core\LazyLoadService;
use Twist\Service\Core\RelativeUrlService;
use Twist\Service\Core\SslCertificatesService;
use Twist\Service\Core\SubresourceIntegrityService;
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
		$app->service(SslCertificatesService::id(), static function (App $app) {
			return new SslCertificatesService($app, App::INIT);
		}, true);

		$app->service(HeadCleanerService::id(), static function (App $app) {
			return new HeadCleanerService($app, App::INIT);
		}, true);

		$app->service(SubresourceIntegrityService::id(), static function (App $app) {
			return new SubresourceIntegrityService($app, App::INIT);
		}, true);

		$app->service(ContentCleanerService::id(), static function (App $app) {
			return new ContentCleanerService($app);
		}, true);

		$app->service(RelativeUrlService::id(), static function (App $app) {
			return new RelativeUrlService($app);
		}, true);

		$app->service(ThumbnailGeneratorService::id(), static function (App $app) {
			return new ThumbnailGeneratorService($app);
		}, true);
	}

}