<?php

namespace Twist\Model\User;

use Exception;
use IteratorAggregate;
use Twist\App\AppException;
use Twist\Model\CollectionIteratorInterface;
use Twist\Model\Pagination\HasPaginationInterface;
use Twist\Model\Pagination\PaginationInterface;
use Twist\Model\Post\Query as PostQuery;
use WP_User_Query;

/**
 * Class Query
 *
 * @package Twist\Model\User
 */
class Query implements HasPaginationInterface, IteratorAggregate
{

	/**
	 * @var WP_User_Query
	 */
	private $query;

	/**
	 * @var Users
	 */
	private $users;

	/**
	 * @var Pagination
	 */
	private $pagination;

	/**
	 * @param array $query
	 *
	 * @return Query
	 */
	public static function main(array $query = []): Query
	{
		$instance = new static();

		$query = array_merge([
			'number' => get_option('posts_per_page'),
		], $query, [
			'paged'       => $instance->current_page(),
			'count_total' => true,
		]);

		return $instance->query($query);
	}

	/**
	 * @param array $query
	 *
	 * @return Query
	 */
	public static function make(array $query = []): Query
	{
		$query = array_merge($query, [
			'count_total' => false,
		]);

		return (new static())->query($query);
	}

	/**
	 * @param array $query
	 *
	 * @return $this
	 *
	 * @see WP_User_Query::prepare_query()
	 */
	public function query(array $query): Query
	{
		$this->query = new WP_User_Query($query);
		$this->users = Users::make($this->query->get_results());

		return $this;
	}

	/**
	 * @return Users
	 */
	public function users(): Users
	{
		return $this->users;
	}

	/**
	 * @inheritDoc
	 */
	public function total(): int
	{
		return (int) $this->query->get_total();
	}

	/**
	 * @inheritDoc
	 */
	public function count(): int
	{
		return count((array) $this->query->get_results());
	}

	/**
	 * @inheritDoc
	 */
	public function per_page(): int
	{
		return (int) $this->query->get('number');
	}

	/**
	 * @inheritDoc
	 */
	public function total_pages(): int
	{
		if (!$this->has_pagination()) {
			return 0;
		}

		return ceil($this->total() / $this->per_page());
	}

	/**
	 * @inheritDoc
	 */
	public function current_page(): int
	{
		try {
			return max(1, (int) PostQuery::main()->get('paged'));
		} catch (AppException $exception) {
		}

		return 1;
	}

	/**
	 * @inheritDoc
	 */
	public function has_pagination(): bool
	{
		if ($this->total() === 0 || $this->per_page() === 0) {
			return false;
		}

		return $this->per_page() < $this->total();
	}

	/**
	 * @inheritDoc
	 */
	public function pagination(): PaginationInterface
	{
		return $this->pagination ?? $this->pagination = new Pagination($this);
	}

	/**
	 * @return CollectionIteratorInterface
	 * @throws Exception
	 */
	public function getIterator(): CollectionIteratorInterface
	{
		return $this->users->getIterator();
	}

}
