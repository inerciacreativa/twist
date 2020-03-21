<?php

namespace Twist\Model\Comment;

use Twist\App\AppException;
use Twist\Model\Post\Query as PostQuery;

/**
 * Class CommentsRoot
 *
 * @package Twist\Model\Comment
 */
class CommentsRoot extends Comments
{

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * CommentsRoot constructor.
	 *
	 * @param Query  $query
	 * @param string $type
	 */
	public function __construct(Query $query, string $type)
	{
		parent::__construct($query);

		$this->type = $type;
	}

	/**
	 * @return int
	 */
	protected function page_count(): int
	{
		static $page_count;

		if (isset($page_count)) {
			return $page_count;
		}

		if ($this->count() === 0) {
			return 0;
		}

		if ($this->type === Query::PINGS) {
			return 1;
		}

		try {
			$per_page = (int) PostQuery::main()->get('comments_per_page');
		} catch (AppException $exception) {
			$per_page = 0;
		}

		if ($per_page === 0) {
			$per_page = (int) get_option('comments_per_page');
		}

		if ($per_page === 0) {
			return 1;
		}

		$page_count = ceil($this->count() / $per_page);

		return $page_count;
	}

	/**
	 * @return int
	 */
	protected function page_first(): int
	{
		$page_order = (string) get_option('default_comments_page');

		return ($page_order === 'newest') ? $this->page_count() : 1;
	}

	/**
	 * @return bool
	 */
	public function has_pagination(): bool
	{
		return $this->page_count() > 1;
	}

	/**
	 * @return Pagination
	 */
	public function pagination(): Pagination
	{
		static $pagination;

		if ($pagination === null) {
			$pagination = new Pagination($this->page_count(), $this->page_first(), $this->query->post()
																							   ->link());
		}

		return $pagination;
	}

}
