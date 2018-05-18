<?php

namespace Twist\Model\Comment;

use Twist\Model\Navigation\Links;
use Twist\Model\Navigation\Pagination;

/**
 * Class CommentPagination
 *
 * @package Twist\Model\Comment
 */
class CommentPagination extends Pagination
{

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
	 * @var array
	 */
	protected $arguments;

	/**
	 * CommentPagination constructor.
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
	}

	/**
	 * @inheritdoc
	 */
	protected function simple_links(): array
	{
		return [
			'prev' => get_previous_comments_link(_x('Previous', 'previous set of posts', 'twist')),
			'next' => get_next_comments_link(_x('Next', 'next set of posts', 'twist'), $this->page_count),
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function paginate_links(array $arguments = []): Links
	{
		$arguments = array_merge([
			'base'         => add_query_arg('cpage', '%#%'),
			'format'       => '',
			'total'        => $this->page_count,
			'current'      => get_query_var('cpage', 1),
			'add_fragment' => '#comments',
		], $arguments);

		if ($this->rewrite()->using_permalinks()) {
			$link   = trailingslashit($this->post_link);
			$append = $this->rewrite()->comments_pagination_base . '-%#%';

			$arguments['base'] = user_trailingslashit($link . $append, 'commentpaged');
		}

		$this->arguments = $arguments;

		add_filter('paginate_links', [$this, 'filter']);
		$links = parent::paginate_links($arguments);
		remove_filter('paginate_links', [$this, 'filter']);

		return $links;
	}

	/**
	 * @param string $link
	 *
	 * @return string
	 */
	public function filter(string $link): string
	{
		if ($this->rewrite()->using_permalinks()) {
			$search = $this->rewrite()->comments_pagination_base . '-' . $this->page_first . '/';
		} else {
			$search = '&#038;cpage=' . $this->page_first;
		}

		return str_replace($search, '', $link);
	}

	/**
	 * @return \WP_Rewrite
	 */
	protected function rewrite(): \WP_Rewrite
	{
		/** @var \WP_Rewrite $wp_rewrite */
		global $wp_rewrite;

		return $wp_rewrite;
	}

}