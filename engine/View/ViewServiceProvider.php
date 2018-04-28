<?php

namespace Twist\View;

use Twist\App\ServiceProviderInterface;
use Twist\App\App;
use Twist\View\Twig\TwigService;

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
		$app->service(TwigService::id(), function (App $app) {
			return new TwigService($app);
		});
	}

}