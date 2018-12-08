<?php

namespace Twist\Model\Site;

use Twist\App\App;
use Twist\Model\Site\Elements\ElementsParser;
use Twist\Model\Site\Elements\Scripts;

/**
 * Class Foot
 *
 * @package Twist\Model\Site
 */
class Foot extends ElementsParser
{

	/**
	 * Head constructor.
	 */
	public function __construct()
	{
		parent::__construct(App::FOOT, [
			Scripts::class,
		]);
	}

}