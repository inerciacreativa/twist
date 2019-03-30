<?php

namespace Twist\Model\Taxonomy;

use Twist\Model\Meta\Meta as BaseMeta;

/**
 * Class Meta
 *
 * @package Twist\Model\Taxonomy
 */
class Meta extends BaseMeta
{

	/**
	 * Meta constructor.
	 *
	 * @param Term $term
	 */
	public function __construct(Term $term)
	{
		parent::__construct($term, 'term');
	}

}