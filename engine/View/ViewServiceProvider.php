<?php

namespace Twist\View;

use Twist\App\App;
use Twist\Service\ServiceProviderInterface;
use Twist\View\Twig\TwigViewService;

/**
 * Class ViewServiceProvider
 *
 * @package Twist\View
 */
class ViewServiceProvider implements ServiceProviderInterface
{

	/**
	 * @inheritdoc
	 */
	public function register(App $app): void
	{
		$app->service(TwigViewService::id(), static function (App $app) {
			return new TwigViewService($app, $app['config'], $app['context']);
		}, true);
	}

}
