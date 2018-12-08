<?php /** @noinspection NullPointerExceptionInspection */

namespace Twist\Model\Navigation;

use Twist\App\AppException;
use Twist\Library\Util\Str;
use Twist\Library\Util\Tag;
use Twist\Model\Post\Query;

/**
 * Class Pagination
 *
 * @package Twist\Model\Navigation
 */
class Pagination
{

	/**
	 * @return bool
	 * @throws AppException
	 */
	public function has_pages(): bool
	{
		return Query::main()->has_pages();
	}

	/**
	 * @return Links
	 * @throws AppException
	 */
	public function simple(): Links
	{
		$links = new Links();
		if (!$this->has_pages()) {
			return $links;
		}

		$items = $this->getLinks();
		$index = 1;

		foreach ($items as $class => $item) {
			$links->add(new Link($this->getLink($index, $item, $class)));
			$index++;
		}

		return $links;
	}

	/**
	 * @param array $arguments
	 *
	 * @return Links
	 * @throws AppException
	 */
	public function extended(array $arguments = []): Links
	{
		return $this->getPaginatedLinks($arguments);
	}

	/**
	 * @param array $arguments
	 *
	 * @return Links
	 * @throws AppException
	 */
	public function numeric(array $arguments = []): Links
	{
		return $this->getPaginatedLinks(array_merge($arguments, ['prev_next' => false]));
	}

	/**
	 * @return array
	 */
	protected function getLinks(): array
	{
		return array_filter([
			'prev' => get_previous_posts_link(_x('Previous', 'previous set of posts', 'twist')),
			'next' => get_next_posts_link(_x('Next', 'next set of posts', 'twist')),
		]);
	}

	/**
	 * @param array $arguments
	 *
	 * @return Links
	 * @throws AppException
	 */
	protected function getPaginatedLinks(array $arguments = []): Links
	{
		$links = new Links();
		if (!$this->has_pages()) {
			return $links;
		}

		$items = paginate_links(array_merge([
			'mid_size'  => 1,
			'prev_text' => _x('Previous', 'previous set of posts', 'twist'),
			'next_text' => _x('Next', 'next set of posts', 'twist'),
		], $arguments, ['type' => 'array']));

		foreach ($items as $index => $item) {
			$links->add(new Link($this->getLink($index + 1, $item)));
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
			'id'      => $index,
			'title'   => $title,
			'url'     => $tag['href'] ?? null,
			'classes' => $class,
			'label'   => $label,
		];
	}

}