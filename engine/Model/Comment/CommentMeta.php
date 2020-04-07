<?php

namespace Twist\Model\Comment;

use Twist\Model\Meta\Meta;

/**
 * Class CommentMeta
 *
 * @package Twist\Model\Comment
 *
 * @method set_parent(Comment $parent)
 * @method Comment parent()
 */
class CommentMeta extends Meta
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
