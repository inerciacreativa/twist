<?php

namespace Twist\Model\Taxonomy;

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
	 * @throws \Exception
	 */
	public function __construct()
	{
		parent::__construct('post_tag');
	}

}