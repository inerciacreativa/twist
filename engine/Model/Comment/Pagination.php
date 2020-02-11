<?php

namespace Twist\Model\Comment;

use Twist\App\AppException;
use Twist\Library\Hook\Hookable;
use Twist\Model\Navigation\Links;
use Twist\Model\Navigation\Pagination as BasePagination;
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
	 * @var array
	 */
	protected $arguments;

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
	protected function getLinks(): array
	{
		return array_filter([
			'prev' => get_previous_comments_link(_x('Previous', 'previous set of posts', 'twist')),
			'next' => get_next_comments_link(_x('Next', 'next set of posts', 'twist'), $this->page_count),
		]);
	}

	/**
	 * @inheritdoc
	 * @throws AppException
	 */
	protected function getPaginatedLinks(array $arguments = []): Links
	{
		if (!$this->has_pages()) {
			return new Links();
		}

		$arguments = array_merge([
			'base'         => add_query_arg('cpage', '%#%'),
			'format'       => '',
			'total'        => $this->total(),
			'current'      => $this->current(),
			'add_fragment' => '#comments',
		], $arguments);

		if ($this->rewrite()->using_permalinks()) {
			$link   = trailingslashit($this->post_link);
			$append = $this->rewrite()->comments_pagination_base . '-%#%';

			$arguments['base'] = user_trailingslashit($link . $append, 'commentpaged');
		}

		$this->arguments = $arguments;

		$this->hook()->on('paginate_links', 'filterLink');
		$links = parent::getPaginatedLinks($arguments);
		$this->hook()->disable();

		return $links;
	}

	/**
	 * @param string $link
	 *
	 * @return string
	 */
	protected function filterLink(string $link): string
	{
		if ($this->rewrite()->using_permalinks()) {
			$search = $this->rewrite()->comments_pagination_base . '-' . $this->page_first . '/';
		} else {
			$search = '&#038;cpage=' . $this->page_first;
		}

		return str_replace($search, '', $link);
	}

	/**
	 * @return WP_Rewrite
	 */
	protected function rewrite(): WP_Rewrite
	{
		/** @var WP_Rewrite $wp_rewrite */ global $wp_rewrite;

		return $wp_rewrite;
	}

}
