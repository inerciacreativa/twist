<?php

namespace Twist\Model\Pagination;

use Twist\Library\Hook\Hookable;
use Twist\Library\Html\Classes;
use Twist\Library\Html\Tag;
use Twist\Library\Support\Str;
use Twist\Model\Link\Links;

/**
 * Class Pagination
 *
 * @package Twist\Model\Pagination
 */
abstract class Pagination implements PaginationInterface
{

	use Hookable;

	protected $classes = [
		'current' => 'is-current',
		'prev'    => 'is-prev',
		'next'    => 'is-next',
		'dots'    => 'is-dots',
	];

	/**
	 * @inheritDoc
	 */
	public function simple(): Links
	{
		$items = $this->getPrevNextLinks();

		return $this->getLinks($items);
	}

	/**
	 * @inheritDoc
	 */
	public function extended(array $arguments = []): Links
	{
		$items = $this->getPaginatedLinks($arguments);

		return $this->getLinks($items);
	}

	/**
	 * @inheritDoc
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
	 * @param array $items
	 *
	 * @return Links
	 */
	protected function getLinks(array $items): Links
	{
		$links = new Links();

		foreach ($items as $index => $item) {
			$links->add($this->getLink($index + 1, $item));
		}

		return $links;
	}

	/**
	 * @param int    $index
	 * @param string $item
	 *
	 * @return Link
	 *
	 * @noinspection NullPointerExceptionInspection
	 */
	protected function getLink(int $index, string $item): Link
	{
		$tag     = Tag::parse(Str::fromEntities($item));
		$title   = $tag->content();
		$classes = $this->getClasses($tag['class']);
		$label   = $this->getLabel($title, $classes);

		return new Link([
			'id'         => $index,
			'title'      => $title,
			'current'    => $classes->has('is-current'),
			'class'      => $classes,
			'href'       => $tag['href'],
			'aria-label' => $label,
		]);
	}

	/**
	 * @param array $classes
	 *
	 * @return Classes
	 */
	protected function getClasses(array $classes): Classes
	{
		return Classes::make($classes)
					  ->only(array_keys($this->classes))
					  ->replace(array_keys($this->classes), $this->classes);
	}

	/**
	 * @param string  $title
	 * @param Classes $classes
	 *
	 * @return string|null
	 */
	protected function getLabel(string $title, Classes $classes): ?string
	{
		$label = sprintf(__('Goto page %s', 'twist'), $title);

		if ($classes->has('is-prev')) {
			$label = __('Goto previous page', 'twist');
		} else if ($classes->has('is-next')) {
			$label = __('Goto next page', 'twist');
		} else if ($classes->has('is-current')) {
			$label = sprintf(__('Page %s', 'twist'), $title);
		} else if ($classes->has('is-dots')) {
			$label = null;
		}

		return $label;
	}

}
