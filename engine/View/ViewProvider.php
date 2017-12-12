<?php

namespace Twist\View;

use Twist\Service\ServiceProviderInterface;
use Twist\App\Application;
use Twist\View\Twig\TwigService;

/**
 * Class ViewProvider
 *
 * @package Twist\View
 */
class ViewProvider implements ServiceProviderInterface
{

	/**
	 * @inheritdoc
	 *
	 * @throws \Pimple\Exception\ExpectedInvokableException
	 * @throws \Pimple\Exception\FrozenServiceException
	 * @throws \Pimple\Exception\InvalidServiceIdentifierException
	 * @throws \Pimple\Exception\UnknownIdentifierException
	 */
	public function register(Application $app)
	{
		$app->service(TwigService::id(), function (Application $app) {
			return new TwigService($app);
		});
	}

}