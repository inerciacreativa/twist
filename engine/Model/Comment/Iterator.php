<?php

namespace Twist\Model\Comment;

use Twist\Model\Base\CollectionIterator;
use Twist\Model\Base\ModelInterface;

/**
 * Class Iterator
 *
 * @package Twist\Model\Comment
 *
 * @property Comment[] $models
 */
class Iterator extends CollectionIterator
{

	/**
	 * @return null|Comment
	 */
	public function current(): ?ModelInterface
	{
		/** @var Comment $comment */
		$comment = parent::current();

		$GLOBALS['comment']       = &$comment;
		$GLOBALS['comment_depth'] = $comment->depth() + 1;

		return $comment;
	}

}