<?php

namespace Twist\Model\Comment;

use Twist\Library\Support\Str;
use Twist\Library\Support\Url;
use Twist\Model\Pagination\Pagination as BasePagination;
use WP_Rewrite;

/**
 * Class Pagination
 *
 * @package Twist\Model\Comment
 */
class Pagination extends BasePagination
{

	private $query;

	/**
	 * Pagination constructor.
	 *
	 * @param Query $query
	 */
	public function __construct(Query $query)
	{
		$this->query = $query;

		$this->hook()
			 ->on('paginate_links', 'filterLink')
			 ->on('previous_comments_link_attributes', static function () {
				 return 'class="prev"';
			 })
			 ->on('next_comments_link_attributes', static function () {
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
			get_previous_comments_link(_x('Previous', 'previous set of comments', 'twist')),
			get_next_comments_link(_x('Next', 'next set of comments', 'twist'), $this->query->total_pages()),
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
			'base'         => $this->getBase($this->query->post()->link()),
			'format'       => '',
			'add_fragment' => '#comments',
			'total'        => $this->query->total_pages(),
			'current'      => $this->query->current_page(),
			'mid_size'     => 1,
			'prev_text'    => _x('Previous', 'previous set of comments', 'twist'),
			'next_text'    => _x('Next', 'next set of comments', 'twist'),
		], $arguments, ['type' => 'array']);

		return paginate_links($arguments);
	}

	/**
	 * Return the base of the paginated URL.
	 *
	 * @param string $link
	 *
	 * @return string
	 */
	private function getBase(string $link): string
	{
		$placeholder = '%#%';

		if ($segment = $this->getSegment($placeholder)) {
			return $link . $segment;
		}

		return $this->addQuery($link, $placeholder);
	}

	/**
	 * Remove the query from the URL for the first page of comments.
	 *
	 * @param string $link
	 *
	 * @return string
	 */
	private function filterLink(string $link): string
	{
		$page = $this->query->first_page();

		if ($segment = $this->getSegment($page)) {
			return Str::replaceLast($link, $segment, '');
		}

		return $this->removeQuery($link, $page);
	}

	/**
	 * @param string $link
	 * @param string $page
	 *
	 * @return string
	 */
	private function addQuery(string $link, string $page): string
	{
		$url = Url::parse($link);

		$url->query['cpage'] = $page;

		return $url;
	}

	/**
	 * @param string $link
	 * @param int    $page
	 *
	 * @return string
	 * @noinspection TypeUnsafeComparisonInspection
	 */
	private function removeQuery(string $link, int $page): string
	{
		$url = Url::parse($link);

		if (isset($url->query['cpage']) && ($url->query['cpage'] == $page)) {
			$url->query = ['cpage' => null];
		}

		return $url;
	}

	/**
	 * @param string $suffix
	 *
	 * @return string|null
	 */
	private function getSegment(string $suffix): ?string
	{
		/** @var WP_Rewrite $wp_rewrite */ global $wp_rewrite;

		if ($wp_rewrite->using_permalinks()) {
			return $wp_rewrite->comments_pagination_base . '-' . $suffix . '/';
		}

		return null;
	}

}
