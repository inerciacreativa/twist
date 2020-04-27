<?php

namespace Twist\App;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Twist\Asset\Fonts;
use Twist\Asset\Manifest;
use Twist\Asset\Queue;
use Twist\Asset\Resources;
use Twist\Service\ServiceProviderInterface;
use Twist\Twist;
use Twist\View\Context;

/**
 * Class AppServiceProvider
 *
 * @package Twist\App
 */
class AppServiceProvider implements ServiceProviderInterface
{

	/**
	 * @inheritdoc
	 */
	public function register(App $app): void
	{
		$app->service('config', static function () {
			return new Config();
		});

		$app->service('logger', static function () {
			$logger = new Logger('Twist');
			if (Twist::isEnv([Twist::DEVELOPMENT, Twist::STAGING])) {
				$logger->pushHandler(new StreamHandler(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'app.log'));
			}

			return $logger;
		});

		$app->service('theme', static function (App $app) {
			return new Theme($app, $app['config']);
		});

		$app->service('asset_manifest', static function (App $app) {
			return new Manifest($app['config']);
		});

		$app->service('asset_queue', static function (App $app) {
			return new Queue($app['asset_resources']);
		});

		$app->service('asset_fonts', static function (App $app) {
			return new Fonts($app['asset_queue'], $app['asset_resources']);
		});

		$app->service('asset_resources', static function () {
			return new Resources();
		});

		$app->service('context', static function (App $app) {
			return new Context($app['config']);
		});

		$app->service('view', static function (App $app) {
			$view = $app['config']->get('view.service');

			return $app[$view];
		});
	}

}
