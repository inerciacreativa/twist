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
	 * @throws \RuntimeException
	 */
	public function __construct()
	{
		parent::__construct('post_tag');
	}

	/**
	 * @inheritdoc
	 */
	public function is_current($term = null): bool
	{
		return is_tag($term);
	}

}