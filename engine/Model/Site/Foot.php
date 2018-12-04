<?php

namespace Twist\Model\Site;

use Twist\App\App;
use Twist\Model\Site\Element\ElementsParser;
use Twist\Model\Site\Element\ElementsRenderer;
use Twist\Model\Site\Element\Script;

/**
 * Class Foot
 *
 * @package Twist\Model\Site
 */
class Foot extends ElementsRenderer
{

	/**
	 * Head constructor.
	 */
	public function __construct()
	{
		parent::__construct(App::FOOT, new ElementsParser([
			Script::class,
		]));
	}

}