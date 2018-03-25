<?php

namespace Twist\Model\Comment;

/**
 * Class Walker
 *
 * @package Twist\Model\Comment
 */
class Walker extends \Walker_Comment
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
	public function __construct(Comments $comments = null)
	{
		$this->root     = $comments;
		$this->comments = $comments;
	}

	/**
	 * @inheritdoc
	 */
	public function start_el(&$output, $comment, $depth = 0, $arguments = [], $id = 0)
	{
		$this->comment = new Comment($this->comments, $comment, $depth);

		$this->comments->add($this->comment);
	}

	/**
	 * @inheritdoc
	 */
	public function end_el(&$output, $comment, $depth = 0, $arguments = [])
	{
	}

	/**
	 * @inheritdoc
	 */
	public function start_lvl(&$output, $depth = 0, $arguments = [])
	{
		$this->comments = $this->comment->children();
	}

	/**
	 * @inheritdoc
	 */
	public function end_lvl(&$output, $depth = 0, $arguments = [])
	{
		$comment  = $this->comments->parent();
		$comments = $comment->has_parent() ? $comment->parent()
		                                             ->children() : $this->root;

		$this->comments = $comments;
	}

}