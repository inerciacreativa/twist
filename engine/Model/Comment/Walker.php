<?php

namespace Twist\Model\Comment;

use Walker_Comment;

/**
 * Class Walker
 *
 * @package Twist\Model\Comment
 */
class Walker extends Walker_Comment
{

	/**
	 * @var Comments
	 */
	protected $root;

	/**
	 * @var Comments
	 */
	protected $comments;

	/**
	 * @var Comment
	 */
	protected $comment;

	/**
	 * Walker constructor.
	 *
	 * @param Comments $comments
	 */
	public function __construct(Comments $comments)
	{
		$this->root = $this->comments = $comments;
	}

	/**
	 * @return Comments
	 */
	public function getComments(): Comments
	{
		return $this->root;
	}

	/**
	 * @inheritdoc
	 */
	public function start_el(&$output, $comment, $depth = 0, $arguments = [], $id = 0): void
	{
		$this->comment = new Comment($this->comments, $comment, $depth);

		$this->comments->add($this->comment);
	}

	/**
	 * @inheritdoc
	 */
	public function end_el(&$output, $comment, $depth = 0, $arguments = []): void
	{
	}

	/**
	 * @inheritdoc
	 */
	public function start_lvl(&$output, $depth = 0, $arguments = []): void
	{
		$this->comments = $this->comment->children();
	}

	/**
	 * @inheritdoc
	 */
	public function end_lvl(&$output, $depth = 0, $arguments = []): void
	{
		$comment = $this->comments->parent();

		/** @noinspection NullPointerExceptionInspection */
		$this->comments = $comment->has_parent() ? $comment->parent()
		                                                   ->children() : $this->root;
	}

}