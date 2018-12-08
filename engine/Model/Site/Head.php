<?php

namespace Twist\Model\Site;

use Twist\App\App;
use Twist\Model\Site\Elements\ElementsParser;
use Twist\Model\Site\Elements\Links;
use Twist\Model\Site\Elements\Metas;
use Twist\Model\Site\Elements\Scripts;
use Twist\Model\Site\Elements\Styles;
use Twist\Model\Site\Elements\Title;

/**
 * Class Head
 *
 * @package Twist\Model\Site
 */
class Head extends ElementsParser
{

	/**
	 * Head constructor.
	 */
	public function __construct()
	{
		parent::__construct(App::HEAD, [
			Title::class,
			Metas::class,
			Links::class,
			Styles::class,
			Scripts::class,
		]);
	}

}