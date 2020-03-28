<?php

namespace Twist\Model\Comment;

use Twist\Model\Collection;
use Twist\Model\CollectionIteratorInterface;

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
		parent::__construct($parent);

		$this->query = $query;
	}

	/**
	 * @return Query
	 */
	public function query(): Query
	{
		return $this->query;
	}

	/**
	 * @return Iterator
	 */
	public function getIterator(): CollectionIteratorInterface
	{
		return new Iterator($this->models);
	}

}
