<?php

namespace Twist\Model\Comment;

use Twist\Model\Meta\Meta as BaseMeta;

/**
 * Class Meta
 *
 * @package Twist\Model\Comment
 *
 * @method set_parent(Comment $parent)
 * @method Comment parent()
 */
class Meta extends BaseMeta
{

	/**
	 * Meta constructor.
	 *
	 * @param Comment $comment
	 */
	public function __construct(Comment $comment)
	{
		parent::__construct($comment, 'comment');
	}

}
