<?php

namespace Twist\Model\Comment;

/**
 * Class CommentWalker
 *
 * @package Twist\Model\Comment
 */
class CommentWalker extends \Walker_Comment
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
	 * CommentWalker constructor.
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
	public function comments(): Comments
	{
		return $this->root;
	}

	/**
	 * @inheritdoc
	 */
	public function start_el(&$output, $comment, $depth = 0, $arguments = [], $id = 0): void
	{
		$this->comment = new Comment($this->comments, $comment, $depth);

		if ($this->comments->has_parent()) {
			$this->comment->set_parent($this->comments->parent());
		}

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