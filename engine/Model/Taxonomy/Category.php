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
     * @throws \RuntimeException
     */
    public function __construct()
    {
        parent::__construct('category');
    }

    /**
     * @inheritdoc
     */
	public function is_current($term = null): bool
	{
		return is_category($term);
	}

}