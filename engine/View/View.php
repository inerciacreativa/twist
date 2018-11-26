<?php

namespace Twist\View;

use Twist\Service\Service;
use Twist\App\App;

/**
 * Class ViewService
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
	 * @param Context $context
	 */
	public function __construct(App $app, Context $context)
	{
		parent::__construct($app);

		$this->context = $context;
	}

	public function context(): Context
	{
		return $this->context;
	}

}