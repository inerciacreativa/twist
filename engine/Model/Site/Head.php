<?php

namespace Twist\Model\Site;

use Twist\App\App;
use Twist\Model\Site\Element\ElementsParser;
use Twist\Model\Site\Element\ElementsRenderer;
use Twist\Model\Site\Element\Link;
use Twist\Model\Site\Element\Meta;
use Twist\Model\Site\Element\Script;
use Twist\Model\Site\Element\Style;
use Twist\Model\Site\Element\Title;

/**
 * Class Head
 *
 * @package Twist\Model\Site
 */
class Head extends ElementsRenderer
{

	/**
	 * Head constructor.
	 */
	public function __construct()
	{
		parent::__construct(App::HEAD, new ElementsParser([
			Title::class,
			Meta::class,
			Link::class,
			Style::class,
			Script::class,
		]));
	}

}