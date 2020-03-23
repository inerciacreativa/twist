<?php

namespace Twist\App;

use Twist\Asset\Fonts;
use Twist\Service\ServiceProviderInterface;
use Twist\View\Context;
use Twist\Asset\Manifest;
use Twist\Asset\Queue;

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

		$app->service('asset_manifest', static function (App $app) {
			return new Manifest($app['config']);
		});

		$app->service('asset_queue', static function () {
			return new Queue();
		});

		$app->service('asset_fonts', static function (App $app) {
			return new Fonts($app['asset_queue']);
		});

		$app->service('theme', static function (App $app) {
			return new Theme($app, $app['config'], $app['asset_queue'], $app['asset_fonts']);
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
