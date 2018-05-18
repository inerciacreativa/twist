<?php /** @noinspection NullPointerExceptionInspection */

namespace Twist\Model\Navigation;

use Twist\Library\Util\Str;
use Twist\Library\Util\Tag;

/**
 * Class Pagination
 *
 * @package Twist\Model\Navigation
 */
class Pagination
{

	/**
	 * @return Links
	 */
	public function simple(): Links
	{
		$links = new Links();
		$items = array_filter($this->simple_links());
		$index = 0;

		foreach ($items as $class => $item) {
			$link = new Link($this->link($index, $item, $class));
			$links->add($link);
			$index++;
		}

		return $links;
	}

	/**
	 * @return array
	 */
	protected function simple_links(): array
	{
		return [
			'prev' => get_previous_posts_link(_x('Previous', 'previous set of posts', 'twist')),
			'next' => get_next_posts_link(_x('Next', 'next set of posts', 'twist')),
		];
	}

	/**
	 * @param array $arguments
	 *
	 * @return Links
	 */
	public function extended(array $arguments = []): Links
	{
		return $this->paginate_links($arguments);
	}

	/**
	 * @param array $arguments
	 *
	 * @return Links
	 */
	public function numeric(array $arguments = []): Links
	{
		return $this->paginate_links(array_merge($arguments, ['prev_next' => false]));
	}

	/**
	 * @param array $arguments
	 *
	 * @return Links
	 */
	protected function paginate_links(array $arguments = []): Links
	{
		$links = new Links();
		$items = (array) paginate_links(array_merge([
			'mid_size'  => 1,
			'prev_text' => _x('Previous', 'previous set of posts', 'twist'),
			'next_text' => _x('Next', 'next set of posts', 'twist'),
		], $arguments, ['type' => 'array']));

		foreach ($items as $index => $item) {
			$link = new Link($this->link($index, $item));
			$links->add($link);
		}

		return $links;
	}

	/**
	 * @param int         $index
	 * @param string      $item
	 * @param string|null $classes
	 *
	 * @return array
	 */
	protected function link(int $index, string $item, string $classes = null): array
	{
		$tag     = Tag::parse(Str::fromEntities($item));
		$classes = $classes ?? trim(str_replace('page-numbers', '', $tag['class']));
		$title   = $tag->content();
		$label   = sprintf(__('Goto page %s', 'twist'), $title);

		if ($classes === 'prev') {
			$label = __('Goto previous page', 'twist');
		} else if ($classes === 'next') {
			$label = __('Goto next page', 'twist');
		} else if ($classes === 'current') {
			$label = sprintf(__('Current page, page %s', 'twist'), $title);
		} else if ($classes === 'dots') {
			$label = '';
		}

		return [
			'id'      => $index,
			'title'   => $title,
			'url'     => $tag['href'] ?? null,
			'classes' => [$classes],
			'label'   => $label,
		];
	}

}