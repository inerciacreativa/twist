<?php

namespace Twist\Model\Comment;

use Twist\Model\Collection;
use Twist\Model\CollectionIteratorInterface;
use Twist\Model\Post\Post;

/**
 * Class Comments
 *
 * @package Twist\Model\Comment
 */
class Comments extends Collection
{

	/**
	 * @var Query
	 */
	protected $query;

	/**
	 * Comments constructor.
	 *
	 * @param Query   $query
	 * @param Comment $parent
	 */
	public function __construct(Query $query, Comment $parent = null)
	{
		$this->query = $query;

		parent::__construct($parent);
	}

	/**
	 * @return Query
	 */
	public function query(): Query
	{
		return $this->query;
	}

	/**
	 * @return Pagination|null
	 */
	public function pagination(): ?Pagination
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
		return new Iterator($this->models);
	}

}
