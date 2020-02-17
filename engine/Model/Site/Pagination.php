<?php

namespace Twist\Model\Site;

use Twist\App\AppException;
use Twist\Model\Pagination\Pagination as BasePagination;
use Twist\Model\Post\Query;

/**
 * Class Pagination
 *
 * @package Twist\Model\Site
 */
class Pagination extends BasePagination
{

	/**
	 * @var Query
	 */
	protected $query;

	/**
	 * Pagination constructor.
	 *
	 * @param Query|null $query
	 *
	 * @throws AppException
	 */
	public function __construct(Query $query = null)
	{
		$this->query = $query ?: Query::main();
	}

	/**
	 * @inheritdoc
	 */
	public function has_pages(): bool
	{
		return $this->query->has_pages();
	}

	/**
	 * @inheritdoc
	 */
	public function total(): int
	{
		return $this->query->total_pages();
	}

	/**
	 * @inheritdoc
	 */
	public function current(): int
	{
		return $this->query->current_page();
	}

	/**
	 * @return int
	 */
	public function posts_total(): int
	{
		return $this->query->total();
	}

	/**
	 * @return int
	 */
	public function posts_per_page(): int
	{
		return $this->query->posts_per_page();
	}

	/**
	 * @inheritdoc
	 */
	protected function getPrevNextLinks(): array
	{
		return array_filter([
			'prev' => get_previous_posts_link(_x('Previous', 'previous set of posts', 'twist')),
			'next' => get_next_posts_link(_x('Next', 'next set of posts', 'twist')),
		]);
	}

	/**
	 * @inheritdoc
	 */
	protected function getPaginatedLinks(array $arguments): array
	{
		$arguments = array_merge([
			'total'     => $this->total(),
			'current'   => $this->current(),
			'mid_size'  => 1,
			'prev_text' => _x('Previous', 'previous set of posts', 'twist'),
			'next_text' => _x('Next', 'next set of posts', 'twist'),
		], $arguments, ['type' => 'array']);

		return paginate_links($arguments);
	}

}
