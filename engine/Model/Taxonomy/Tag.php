<?php

namespace Twist\Model\Taxonomy;

use Twist\App\AppException;

/**
 * Class Tag
 *
 * @package Twist\Model\Taxonomy
 */
class Tag extends Taxonomy
{

	/**
	 * Category constructor.
	 *
	 * @throws AppException
	 */
	public function __construct()
	{
		parent::__construct('post_tag');
	}

}