<?php

namespace Twist\Model\Comment;

use Twist\Model\Collection;
use Twist\Model\CollectionIteratorInterface;

/**
 * Class Comments
 *
 * @package Twist\Model\Comment
 *
 * @method Comment|null parent()
 * @method Comment|null get(int $id)
 * @method Comment[] all()
 * @method Comment|null first(callable $callback = null, $default = null)
 * @method Comment|null last(callable $callback = null, $default = null)
 * @method Comments merge($models)
 * @method Comments only(array $ids)
 * @method Comments except(array $ids)
 * @method Comments slice(int $offset, int $length = null)
 * @method Comments take(int $limit)
 * @method Comments filter(callable $callback)
 * @method Comments where(string $method, string $operator, $value = null)
 * @method Comments sort(string $method = null, bool $descending = false, int $options = SORT_REGULAR)
 * @method Comments shuffle()
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
