<?php

namespace Twist\Model\Comment;

use Twist\Model\CollectionIterator;
use Twist\Model\ModelInterface;

/**
 * Class Iterator
 *
 * @package Twist\Model\Comment
 *
 * @property Comment[] $models
 *
 * @method Comment|null offsetGet($id)
 */
class Iterator extends CollectionIterator
{

	/**
	 * @return Comment|null
	 */
	public function current(): ?ModelInterface
	{
		/** @var Comment $comment */
		if ($comment = parent::current()) {
			$GLOBALS['comment']       = &$comment;
			$GLOBALS['comment_depth'] = $comment->depth() + 1;
		}

		return $comment;
	}

}
