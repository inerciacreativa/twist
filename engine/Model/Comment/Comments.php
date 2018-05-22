<?php

namespace Twist\Model\Comment;

use Twist\Library\Model\Collection;
use Twist\Library\Model\CollectionIteratorInterface;
use Twist\Model\Post\Post;

/**
 * Class Comments
 *
 * @package Twist\Model\Comment
 *
 * @method Comment|null parent()
 * @method Comment get(int $id)
 * @method Comment|null first(callable $callback = null)
 * @method Comment|null last(callable $callback = null)
 * @method Comments only(array $ids)
 * @method Comments except(array $ids)
 * @method Comments slice(int $offset, int $length = null)
 * @method Comments take(int $limit)
 */
class Comments extends Collection
{

	/**
	 * @var CommentQuery
	 */
	protected $query;

	/**
	 * Comments constructor.
	 *
	 * @param CommentQuery $query
	 * @param Comment      $parent
	 */
	public function __construct(CommentQuery $query, Comment $parent = null)
	{
		$this->query = $query;

		parent::__construct($parent);
	}

	/**
	 * @return CommentQuery
	 */
	public function query(): CommentQuery
	{
		return $this->query;
	}

	/**
	 * @return CommentPagination|null
	 */
	public function pagination(): ?CommentPagination
	{
		return $this->query->pagination();
	}

	/**
	 * @return Post
	 */
	public function post(): Post
	{
		return $this->query->post();
	}

	/**
	 * @inheritdoc
	 */
	public function getIterator(): CollectionIteratorInterface
	{
		return new CommentsIterator($this->models);
	}

}