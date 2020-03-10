<?php

namespace Twist\Model\Navigation;

use Twist\App\AppException;
use Twist\Library\Hook\Hook;
use Twist\Library\Html\Classes;
use Twist\Model\Link\Links;
use Twist\Model\Taxonomy\Taxonomy;
use Twist\Model\Taxonomy\Term;
use Walker_Nav_Menu;

/**
 * Class Walker
 *
 * @package Twist\Model\Navigation
 */
class Builder extends Walker_Nav_Menu
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
	 * @var Link
	 */
	protected $link;

	/**
	 * @var array
	 */
	protected $classes = [
		'current-menu-item'     => 'is-current',
		'current-menu-parent'   => 'is-current-parent',
		'current-menu-ancestor' => 'is-current-ancestor',
		'has-children'          => 'has-dropdown',
	];

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
	 * @inheritdoc
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
	 * @inheritdoc
	 */
	public function start_el(&$output, $item, $depth = 0, $arguments = [], $id = 0): void
	{
		$arguments = Hook::apply('nav_menu_item_args', $arguments, $item, $depth);

		$link = new Link([
			'id'      => $item->ID,
			'title'   => $this->getTitle($item, $arguments, $depth),
			'current' => $item->current,
			'class'   => $this->getClasses($item, $arguments, $depth),
			'href'    => $item->url,
			'rel'     => $item->xfn,
		]);

		$this->link = $item->has_children ? $this->addChildrenTerms($link, $item) : $link;

		if ($this->links->has_parent()) {
			$this->link->set_parent($this->links->parent());
		}

		$this->links->add($this->link);
	}

	/**
	 * @inheritdoc
	 */
	public function end_el(&$output, $item, $depth = 0, $arguments = []): void
	{
	}

	/**
	 * @inheritdoc
	 */
	public function start_lvl(&$output, $depth = 0, $arguments = []): void
	{
		$this->links = $this->link->children();
	}

	/**
	 * @inheritdoc
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
	 * @return Classes
	 */
	protected function getClasses(object $item, object $arguments, int $depth): Classes
	{
		$classes = empty($item->classes) ? [] : (array) $item->classes;
		$classes = Hook::apply('nav_menu_css_class', $classes, $item, $arguments, $depth);
		$classes = Classes::make($classes)
						  ->only(array_keys($this->classes))
						  ->replace(array_keys($this->classes), $this->classes);

		return $classes;
	}

	/**
	 * @param Link   $link
	 * @param object $item
	 *
	 * @return Link
	 * @noinspection NullPointerExceptionInspection
	 */
	protected function addChildrenTerms(Link $link, object $item): Link
	{
		try {
			$taxonomy = new Taxonomy($item->object);
			$terms    = $taxonomy->terms(['child_of' => $item->object_id]);

			$link->classes()->add('has-dropdown');

			/** @var Term $term */
			foreach ($terms as $term) {
				if ($term->is_current()) {
					$link->classes()->add('is-current-parent');
				}

				$link->children()->add(new Link([
					'id'      => $term->id(),
					'title'   => $term->name(),
					'href'    => $term->link(),
					'class'   => $term->is_current() ? 'is-current' : null,
					'current' => $term->is_current(),
				]));
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
	 */
	protected function hasChildrenTerms(object $item, object $arguments): bool
	{
		/** @var /wpdb $wpdb */ global $wpdb;

		if (!in_array($item->object, $arguments->terms_children, true)) {
			return false;
		}

		$query = $wpdb->prepare(/** @lang text */ "SELECT COUNT(*) FROM $wpdb->term_taxonomy WHERE parent = %d", $item->object_id);
		$check = $wpdb->get_results($query);

		return ($check) ? true : false;
	}

}
