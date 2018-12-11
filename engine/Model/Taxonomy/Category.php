<?php

namespace Twist\Model\Taxonomy;

use Twist\App\AppException;

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
	 * @throws AppException
	 */
	public function __construct()
	{
		parent::__construct('category');
	}

}