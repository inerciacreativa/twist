<?php

namespace Twist\Model\Post;

use Twist\Model\Pagination\Pagination as BasePagination;

/**
 * Class Pagination
 *
 * @package Twist\Model\Post
 */
class Pagination extends BasePagination
{

	/**
	 * @var Query
	 */
	private $query;

	/**
	 * Pagination constructor.
	 *
	 * @param Query $query
	 */
	public function __construct(Query $query)
	{
		$this->query = $query;

		$this->hook()->on('previous_posts_link_attributes', static function () {
			return 'class="prev"';
		})->on('next_posts_link_attributes', static function () {
			return 'class="next"';
		});
	}

	/**
	 * @inheritDoc
	 */
	protected function getPrevNextLinks(): array
	{
		if (!$this->query->has_pagination()) {
			return [];
		}

		return array_filter([
			get_previous_posts_link(_x('Previous', 'previous set of posts', 'twist')),
			get_next_posts_link(_x('Next', 'next set of posts', 'twist')),
		]);
	}

	/**
	 * @inheritDoc
	 */
	protected function getPaginatedLinks(array $arguments): array
	{
		if (!$this->query->has_pagination()) {
			return [];
		}

		$arguments = array_merge([
			'total'     => $this->query->total_pages(),
			'current'   => $this->query->current_page(),
			'mid_size'  => 1,
			'prev_text' => _x('Previous', 'previous set of posts', 'twist'),
			'next_text' => _x('Next', 'next set of posts', 'twist'),
		], $arguments, ['type' => 'array']);

		return paginate_links($arguments);
	}

}
