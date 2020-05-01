<?php

namespace Twist\App;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Twist\Service\ServiceProviderInterface;
use Twist\Twist;

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

		$app->service('view', static function (App $app) {
			$view = $app['config']->get('view.service');

			return $app[$view];
		});
	}

}
