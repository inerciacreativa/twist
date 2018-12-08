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