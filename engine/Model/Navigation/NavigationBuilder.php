<?php

namespace Twist\Model\Navigation;

use Twist\App\AppException;
use Twist\Library\Hook\Hook;
use Twist\Model\Link\Links;
use Twist\Model\Taxonomy\Taxonomy;
use Twist\Model\Taxonomy\Term;
use Walker_Nav_Menu;

/**
 * Class Walker
 *
 * @package Twist\Model\Navigation
 */
class NavigationBuilder extends Walker_Nav_Menu
{

	/**
	 * @var Links
	 */
	protected $root;

	/**
	 * @var Links
	 */
	protected $links;

	/**
	 * @var NavigationLink
	 */
	protected $link;

	/**
	 * @param array  $items
	 * @param object $arguments
	 *
	 * @return Links
	 */
	public static function getLinks(array $items, object $arguments): Links
	{
		$links = new Links();
		if (empty($items)) {
			return $links;
		}

		$builder = new static($links);
		$builder->walk($items, $arguments->depth, $arguments);

		return $links;
	}

	/**
	 * Walker constructor.
	 *
	 * @param Links $links
	 */
	protected function __construct(Links $links)
	{
		$this->root = $this->links = $links;
	}

	/**
	 * @inheritDoc
	 */
	public function display_element($item, &$children_elements, $max_depth, $depth, $arguments, &$output): void
	{
		$item->has_children = false;

		if ($depth === 0) {
			$item->has_children = $this->hasChildrenTerms($item, $arguments[0]);
		}

		parent::display_element($item, $children_elements, $max_depth, $depth, $arguments, $output);
	}

	/**
	 * @inheritDoc
	 */
	public function start_el(&$output, $item, $depth = 0, $arguments = [], $id = 0): void
	{
		$arguments = Hook::apply('nav_menu_item_args', $arguments, $item, $depth);

		$link = new NavigationLink([
			'id'      => $item->ID,
			'title'   => $this->getTitle($item, $arguments, $depth),
			'current' => $item->current,
			'class'   => $this->getClasses($item, $arguments, $depth),
			'href'    => $item->url,
			'rel'     => $item->xfn,
		], $this->links->has_parent() ? $this->links->parent() : null);

		$this->link = $item->has_children ? $this->addChildrenTerms($link, $item) : $link;

		$this->links->add($this->link);
	}

	/**
	 * @inheritDoc
	 */
	public function end_el(&$output, $item, $depth = 0, $arguments = []): void
	{
	}

	/**
	 * @inheritDoc
	 */
	public function start_lvl(&$output, $depth = 0, $arguments = []): void
	{
		$this->links = $this->link->children();
	}

	/**
	 * @inheritDoc
	 * @noinspection NullPointerExceptionInspection
	 */
	public function end_lvl(&$output, $depth = 0, $arguments = []): void
	{
		$link = $this->links->parent();

		$this->links = $link->has_parent() ? $link->parent()
												  ->children() : $this->root;
	}

	/**
	 * @param object $item
	 * @param object $arguments
	 * @param int    $depth
	 *
	 * @return string
	 */
	protected function getTitle(object $item, object $arguments, int $depth): string
	{
		$title = Hook::apply('the_title', $item->title, $item->ID);
		$title = Hook::apply('nav_menu_item_title', $title, $item, $arguments, $depth);

		return $title;
	}

	/**
	 * @param object $item
	 * @param object $arguments
	 * @param int    $depth
	 *
	 * @return array
	 */
	protected function getClasses(object $item, object $arguments, int $depth): array
	{
		$classes = empty($item->classes) ? [] : (array) $item->classes;
		$classes = Hook::apply('nav_menu_css_class', array_filter($classes), $item, $arguments, $depth);

		return $classes;
	}

	/**
	 * @param NavigationLink $link
	 * @param object         $item
	 *
	 * @return NavigationLink
	 * @noinspection NullPointerExceptionInspection
	 */
	protected function addChildrenTerms(NavigationLink $link, object $item): NavigationLink
	{
		try {
			$taxonomy = new Taxonomy($item->object);
			$terms    = $taxonomy->terms(['child_of' => $item->object_id]);

			/** @var Term $term */
			foreach ($terms as $term) {
				$child = new NavigationLink([
					'id'      => $term->id(),
					'title'   => $term->name(),
					'href'    => $term->link(),
					'class'   => $term->is_current() ? ['current-menu-item'] : [],
					'current' => $term->is_current(),
				], $link);

				$link->children()->add($child);
			}
		} catch (AppException $exception) {
		}

		return $link;
	}

	/**
	 * @param object $item
	 * @param object $arguments
	 *
	 * @return bool
	 * @noinspection SqlResolve
	 */
	protected function hasChildrenTerms(object $item, object $arguments): bool
	{
		/** @var /wpdb $wpdb */ global $wpdb;

		if (!in_array($item->object, $arguments->terms_children, true)) {
			return false;
		}

		$query = $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->term_taxonomy WHERE parent = %d", $item->object_id);
		$check = $wpdb->get_results($query);

		return ($check) ? true : false;
	}

}
