<?php

namespace Twist\Model\Taxonomy;

use Twist\Model\Meta\Meta;

/**
 * Class TermMeta
 *
 * @package Twist\Model\Taxonomy
 *
 * @method set_parent(Term $parent)
 * @method Term parent()
 */
class TermMeta extends Meta
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
