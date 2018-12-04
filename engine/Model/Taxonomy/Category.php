<?php

namespace Twist\Model\Taxonomy;

/**
 * Class Category
 *
 * @package Twist\Model\Taxonomy
 */
class Category extends Taxonomy
{

	/**
	 * Category constructor.
	 *
	 * @throws \Exception
	 */
	public function __construct()
	{
		parent::__construct('category');
	}

}