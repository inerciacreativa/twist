<?php

namespace Twist\Model\Navigation;

use ic\Framework\Html\Tag;
use Twist\Library\Util\Str;

/**
 * Class Pagination
 *
 * @package Twist\Model\Navigation
 */
class Pagination
{

	/**
	 * @param array $arguments
	 *
	 * @return Links
	 */
	public function make(array $arguments = []): Links
	{
		$links = new Links();

		if ($GLOBALS['wp_query']->max_num_pages > 1) {
			$options = array_merge([
				'mid_size'  => 1,
				'prev_text' => _x('Previous', 'previous set of posts', 'twist'),
				'next_text' => _x('Next', 'next set of posts', 'twist'),
			], $arguments, ['type' => 'array']);

			$items = paginate_links($options);

			foreach ($items as $index => $item) {
				$links->add(new Link($links, $this->getLink($index, $item)));
			}
		}

		return $links;
	}

	/**
	 * @param array $arguments
	 *
	 * @return Links
	 */
	public function numeric(array $arguments = []): Links
	{
		return $this->make(array_merge($arguments, ['prev_next' => false]));
	}

	/**
	 * @return Links
	 */
	public function simple(): Links
	{
		$links = new Links();

		if ($item = get_previous_posts_link(_x('Previous', 'previous set of posts', 'twist'))) {
			$links->add(new Link($links, $this->getLink(0, $item, 'prev')));
		}

		if ($item = get_next_posts_link(_x('Next', 'next set of posts', 'twist'))) {
			$links->add(new Link($links, $this->getLink(1, $item, 'next')));
		}

		return $links;
	}

	/**
	 * @param int         $index
	 * @param string      $item
	 * @param string|null $class
	 *
	 * @return array
	 */
	protected function getLink(int $index, string $item, string $class = null): array
	{
		$tag   = Tag::parse(Str::fromEntities($item));
		$class = $class ?? trim(str_replace('page-numbers', '', $tag['class']));
		$title = $tag->content();
		$label = sprintf(__('Goto page %s', 'twist'), $title);

		if ($class === 'prev') {
			$label = __('Goto previous page', 'twist');
		} else if ($class === 'next') {
			$label = __('Goto next page', 'twist');
		} else if ($class === 'current') {
			$label = sprintf(__('Current page, page %s', 'twist'), $title);
		} else if ($class === 'dots') {
			$label = '';
		}

		return [
			'id'          => $index,
			'label'       => $label,
			'title'       => $title,
			'url'         => $tag['href'] ?? false,
			'is_current'  => $class === 'current',
			'is_disabled' => $class === 'dots',
			'is_next'     => $class === 'next',
			'is_previous' => $class === 'prev',
		];
	}

}