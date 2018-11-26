<?php

namespace Twist\View;

use Twist\App\App;
use Twist\Service\ServiceProviderInterface;
use Twist\View\Twig\TwigView;

/**
 * Class ViewProvider
 *
 * @package Twist\View
 */
class ViewServiceProvider implements ServiceProviderInterface
{

	/**
	 * @inheritdoc
	 *
	 * @throws \Pimple\Exception\ExpectedInvokableException
	 * @throws \Pimple\Exception\FrozenServiceException
	 * @throws \Pimple\Exception\InvalidServiceIdentifierException
	 * @throws \Pimple\Exception\UnknownIdentifierException
	 */
	public function register(App $app): void
	{
		$app->service(Context::id(), function (App $app) {
			return new Context($app);
		});

		$app->service(TwigView::id(), function (App $app) {
			return new TwigView($app, $app[Context::id()]);
		});
	}

}