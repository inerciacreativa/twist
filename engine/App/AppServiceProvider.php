<?php

namespace Twist\App;

use Twist\Service\ServiceProviderInterface;

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

		$app->service('assets', static function (App $app) {
			return new Assets($app['config']);
		});

		$app->service('theme', static function (App $app) {
			return new Theme($app, $app['config']);
		});

		$app->service('context', static function (App $app) {
			return new Context($app);
		});

		$app->service('view', static function (App $app) {
			$view = $app['config']->get('view.service');

			return $app[$view];
		});
	}

}
