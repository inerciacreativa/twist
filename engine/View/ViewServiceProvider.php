<?php

namespace Twist\View;

use Twist\App\App;
use Twist\Service\ServiceProviderInterface;
use Twist\View\Twig\TwigView;

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
		$app->service(TwigView::id(), static function (App $app) {
			return new TwigView($app, $app['config'], $app['context']);
		}, true);
	}

}
