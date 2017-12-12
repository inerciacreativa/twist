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
	public function get(array $arguments = []): Links
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
				$tag  = $this->getTag($item);
				$link = new Link($links, $this->getItem($index, $tag));
				$links->add($link);
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
		return $this->get(array_merge($arguments, ['prev_next' => false]));
	}

	/**
	 * @return Links
	 */
	public function simple(): Links
	{
		$links = new Links();

		if ($prevItem = get_previous_posts_link(_x('Previous', 'previous set of posts', 'twist'))) {
			$prevTag          = $this->getTag($prevItem);
			$prevTag['class'] = 'prev';
			$prevLink         = new Link($links, $this->getItem(0, $prevTag));

			$links->add($prevLink);
		}

		if ($nextItem = get_next_posts_link(_x('Next', 'next set of posts', 'twist'))) {
			$nextTag          = $this->getTag($nextItem);
			$nextTag['class'] = 'next';
			$nextLink         = new Link($links, $this->getItem(1, $nextTag));

			$links->add($nextLink);
		}

		return $links;
	}

	/**
	 * @param int $index
	 * @param Tag $tag
	 *
	 * @return array
	 */
	protected function getItem(int $index, Tag $tag): array
	{
		$title = $tag->content();
		$label = sprintf(__('Goto page %s', 'twist'), $title);
		$class = trim(str_replace('page-numbers', '', $tag['class']));

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
			'id'        => $index,
			'classes'   => [$class],
			'is_active' => $class === 'current',
			'label'     => $label,
			'url'       => $tag['href'] ?? '',
			'title'     => $title,
		];
	}

	/**
	 * @param string $item
	 *
	 * @return Tag
	 */
	protected function getTag(string $item): Tag
	{
		return Tag::parse(Str::fromEntities($item));
	}

}