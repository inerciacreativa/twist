<?php

namespace Twist\Service;

use Twist\App\Action;
use Twist\App\App;
use Twist\Service\Core\ContentCleanerService;
use Twist\Service\Core\HeadCleanerService;
use Twist\Service\Core\SslCertificatesService;
use Twist\Service\Core\SubresourceIntegrityService;
use Twist\Service\Core\ThumbnailGeneratorService;
use Twist\Twist;

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
		if (Twist::isEnv(Twist::DEVELOPMENT)) {
			$app->service(SslCertificatesService::id(), static function (App $app) {
				return new SslCertificatesService($app['config'], Action::INIT);
			}, true);
		}

		$app->service(HeadCleanerService::id(), static function (App $app) {
			return new HeadCleanerService($app['config'], Action::INIT);
		}, true);

		$app->service(SubresourceIntegrityService::id(), static function (App $app) {
			return new SubresourceIntegrityService($app['config'], Action::INIT);
		}, true);

		$app->service(ContentCleanerService::id(), static function (App $app) {
			return new ContentCleanerService($app['config']);
		}, true);

		$app->service(ThumbnailGeneratorService::id(), static function (App $app) {
			return new ThumbnailGeneratorService($app['config']);
		}, true);
	}

}
