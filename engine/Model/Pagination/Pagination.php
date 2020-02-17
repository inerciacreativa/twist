<?php

namespace Twist\Model\Pagination;

use Kint\Kint;
use Twist\Library\Html\Tag;
use Twist\Library\Support\Str;
use Twist\Model\Link\Link;
use Twist\Model\Link\Links;

/**
 * Class Pagination
 *
 * @package Twist\Model\Navigation
 */
abstract class Pagination implements PaginationInterface
{

	/**
	 * @return Links
	 */
	public function simple(): Links
	{
		$links = new Links();
		if (!$this->has_pages()) {
			return $links;
		}

		$items = $this->getPrevNextLinks();
		$index = 1;

		foreach ($items as $class => $item) {
			$links->add($this->getLink($index, $item, $class));
			$index++;
		}

		return $links;
	}

	/**
	 * @param array $arguments
	 *
	 * @return Links
	 */
	public function extended(array $arguments = []): Links
	{
		$links = new Links();
		if (!$this->has_pages()) {
			return $links;
		}

		$items = $this->getPaginatedLinks($arguments);

		foreach ($items as $index => $item) {
			$links->add($this->getLink($index + 1, $item));
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
		return $this->extended(array_merge($arguments, ['prev_next' => false]));
	}

	/**
	 * @return array
	 */
	abstract protected function getPrevNextLinks(): array;

	/**
	 * @param array $arguments
	 *
	 * @return array
	 */
	abstract protected function getPaginatedLinks(array $arguments): array;

	/**
	 * @param int         $index
	 * @param string      $item
	 * @param string|null $class
	 *
	 * @return Link
	 */
	protected function getLink(int $index, string $item, string $class = null): Link
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

		return new Link([
			'id'      => $index,
			'title'   => $title,
			'url'     => $tag['href'] ?? null,
			'classes' => $class,
			'label'   => $label,
		]);
	}

}
