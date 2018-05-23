<?php

namespace Twist\Model\Navigation;

use Twist\Library\Hook\Hook;

/**
 * Class Walker
 *
 * @package Twist\Model\Navigation
 */
class NavigationWalker extends \Walker_Nav_Menu
{

	/**
	 * @var array
	 */
	protected static $classes = [
		'current-menu-item'     => 'current',
		'current-menu-parent'   => 'current-parent',
		'current-menu-ancestor' => 'current-ancestor',
		'has-children'          => 'has-children',
	];

	/**
	 * @var Links
	 */
	private $root;

	/**
	 * @var Links
	 */
	private $links;

	/**
	 * @var Link
	 */
	private $link;

	/**
	 * Walker constructor.
	 *
	 * @param Links $links
	 */
	public function __construct(Links $links)
	{
		$this->root = $this->links = $links;
	}

	/**
	 * @return Links
	 */
	public function navigation(): Links
	{
		return $this->root;
	}

	/**
	 * @inheritdoc
	 */
	public function start_el(&$output, $item, $depth = 0, $arguments = [], $id = 0): void
	{
		$classes = empty($item->classes) ? [] : (array) $item->classes;
		// Remove unneeded classes
		$classes = array_intersect($classes, array_keys(static::$classes));
		// Apply filter and convert to string
		$classes = Hook::apply('nav_menu_css_class', $classes, $item, $arguments, $depth);
		// Replace class names
		$classes = str_replace(array_keys(static::$classes), static::$classes, implode(' ', $classes));
		$classes = explode(' ', $classes);

		$title = Hook::apply('the_title', $item->title, $item->ID);
		$title = Hook::apply('nav_menu_item_title', $title, $item, $arguments, $depth);

		$this->link = new Link([
			'id'      => (int) $item->ID,
			'title'   => $title,
			'url'     => $item->url,
			'classes' => $classes,
			'rel'     => $item->xfn,
		]);

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
	 */
	public function end_lvl(&$output, $depth = 0, $arguments = []): void
	{
		$link = $this->links->parent();

		/** @noinspection NullPointerExceptionInspection */
		$this->links = $link->has_parent() ? $link->parent()
		                                          ->children() : $this->root;
	}

}