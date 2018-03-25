<?php

namespace Twist\App;

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
	public function register(App $app)
	{
		$app->service('config', function () {
			return new Config();
		});

		$app->service('theme', function () {
			return new Theme();
		});

		$app->service('view', function (App $app) {
			return $app[$app['config']->get('view.service')];
		});
	}

}