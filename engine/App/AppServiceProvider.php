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
		$app->service('config', function () {
			return new Config();
		});

		$app->service('asset', function (App $app) {
			return new Asset($app['config']);
		});

		$app->service('theme', function (App $app) {
			return new Theme($app, $app['config'], $app['asset']);
		});

		$app->service('context', function (App $app) {
			return new Context($app);
		});

		$app->service('view', function (App $app) {
			$view = $app['config']->get('view.service');

			return $app[$view];
		});
	}

}