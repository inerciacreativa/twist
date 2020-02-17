<?php

namespace Twist\Model\Comment;

use Twist\Library\Hook\Hookable;
use Twist\Model\Pagination\Pagination as BasePagination;
use Twist\Model\Post\Query;
use WP_Rewrite;

/**
 * Class Pagination
 *
 * @package Twist\Model\Comment
 */
class Pagination extends BasePagination
{

	use Hookable;

	/**
	 * @var int
	 */
	protected $page_count;

	/**
	 * @var int
	 */
	protected $page_first;

	/**
	 * @var string
	 */
	protected $post_link;

	/**
	 * Pagination constructor.
	 *
	 * @param int    $page_count
	 * @param int    $page_first
	 * @param string $post_link
	 */
	public function __construct(int $page_count, int $page_first, string $post_link)
	{
		$this->page_count = $page_count;
		$this->page_first = $page_first;
		$this->post_link  = $post_link;

		$this->hook()
			 ->on('paginate_links', 'filterLink');
	}

	/**
	 * @inheritdoc
	 */
	public function has_pages(): bool
	{
		return $this->page_count > 1;
	}

	/**
	 * @inheritdoc
	 */
	public function total(): int
	{
		return $this->page_count;
	}

	/**
	 * @inheritdoc
	 */
	public function current(): int
	{
		return (int) Query::main()->get('cpage', 1);
	}

	/**
	 * @inheritdoc
	 */
	protected function getPrevNextLinks(): array
	{
		return array_filter([
			'prev' => get_previous_comments_link(_x('Previous', 'previous set of posts', 'twist')),
			'next' => get_next_comments_link(_x('Next', 'next set of posts', 'twist'), $this->page_count),
		]);
	}

	/**
	 * @inheritdoc
	 */
	protected function getPaginatedLinks(array $arguments): array
	{
		$arguments = array_merge([
			'base'         => $this->getBase(),
			'format'       => '',
			'add_fragment' => '#comments',
			'total'        => $this->total(),
			'current'      => $this->current(),
			'mid_size'     => 1,
			'prev_text'    => _x('Previous', 'previous set of posts', 'twist'),
			'next_text'    => _x('Next', 'next set of posts', 'twist'),
		], $arguments, ['type' => 'array']);

		return paginate_links($arguments);
	}

	/**
	 * Remove the query from the URL for the first page of comments.
	 *
	 * @param string $link
	 *
	 * @return string
	 */
	protected function filterLink(string $link): string
	{
		/** @var WP_Rewrite $wp_rewrite */ global $wp_rewrite;

		if ($wp_rewrite->using_permalinks()) {
			$search = $wp_rewrite->comments_pagination_base . '-' . $this->page_first . '/';

			return str_replace($search, '', $link);
		}

		if (strpos($link,'cpage=' . $this->page_first)) {
			return add_query_arg('cpage', false, $link);
		}

		return $link;
	}

	/**
	 * Return the base of the paginated URL.
	 *
	 * @return string
	 */
	protected function getBase(): string
	{
		/** @var WP_Rewrite $wp_rewrite */ global $wp_rewrite;

		if ($wp_rewrite->using_permalinks()) {
			return $this->post_link . $wp_rewrite->comments_pagination_base . '-%#%/';
		}

		return add_query_arg('cpage', '%#%', $this->post_link);
	}

}
