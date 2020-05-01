<?php

namespace Twist\Asset;

use Twist\App\App;
use Twist\Service\ServiceProviderInterface;

/**
 * Class AssetServiceProvider
 *
 * @package Twist\Asset
 */
class AssetServiceProvider implements ServiceProviderInterface
{

	public function register(App $app): void
	{
		$app->service('asset_manifest', static function (App $app) {
			return new Manifest($app['config']);
		});

		$app->service('asset_queue', static function (App $app) {
			return new Queue($app['asset_resources']);
		});

		$app->service('asset_fonts', static function (App $app) {
			return new Fonts($app['asset_queue'], $app['asset_resources']);
		});

		$app->service('asset_google', static function (App $app) {
			return new GoogleFonts($app['asset_resources']);
		});

		$app->service('asset_resources', static function () {
			return new Resources();
		});
	}

}
