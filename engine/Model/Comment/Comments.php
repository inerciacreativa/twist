<?php

namespace Twist\Model\Comment;

use Twist\Model\Collection;

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

}
