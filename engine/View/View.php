<?php

namespace Twist\View;

use Twist\App\App;
use Twist\App\Config;
use Twist\App\Context;
use Twist\Service\Service;

/**
 * Class View
 *
 * @package Twist\View
 */
abstract class View extends Service implements ViewInterface
{

	/**
	 * @var Context
	 */
	protected $context;

	/**
	 * View constructor.
	 *
	 * @param App     $app
	 * @param Config  $config
	 * @param Context $context
	 */
	public function __construct(App $app, Config $config, Context $context)
	{
		parent::__construct($app, App::INIT);

		$this->config  = $config;
		$this->context = $context;
	}

	/**
	 * @inheritdoc
	 */
	public function boot(): bool
	{
		return $this->config->get('view.service') === static::id();
	}

}