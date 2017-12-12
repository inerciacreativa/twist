<?php

namespace Twist\Model\Navigation;

/**
 * Class Walker
 *
 * @package Twist\Model\Navigation
 */
class Walker extends \Walker_Nav_Menu
{

	/**
	 * @var array
	 */
	protected static $classes = [
		'current-menu-item'     => 'active',
		'current-menu-parent'   => 'active-parent',
		'current-menu-ancestor' => 'active-ancestor',
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
		$this->root  = $links;
		$this->links = $links;
	}

	/**
	 * @inheritdoc
	 */
	public function start_el(&$output, $item, $depth = 0, $arguments = [], $id = 0)
	{
		$classes = empty($item->classes) ? [] : (array) $item->classes;
		// Remove unneeded classes
		$classes = array_intersect($classes, array_keys(static::$classes));
		// Apply filter and convert to string
		$classes = apply_filters('nav_menu_css_class', $classes, $item, $arguments, $depth);
		// Replace class names
		$classes = str_replace(array_keys(static::$classes), static::$classes, implode(' ', $classes));
		$classes = explode(' ', $classes);

		$title = apply_filters('the_title', $item->title, $item->ID);
		$title = apply_filters('nav_menu_item_title', $title, $item, $arguments, $depth);

		$this->link = new Link($this->links, [
			'id'        => $item->ID,
			'classes'   => $classes,
			'is_active' => \in_array('active', $classes, false),
			'rel'       => $item->xfn,
			'url'       => $item->url,
			'title'     => $title,
		]);

		$this->links->add($this->link);
	}

	/**
	 * @inheritdoc
	 */
	public function end_el(&$output, $item, $depth = 0, $arguments = [])
	{
	}

	/**
	 * @inheritdoc
	 */
	public function start_lvl(&$output, $depth = 0, $arguments = [])
	{
		$this->links = $this->link->children();
	}

	/**
	 * @inheritdoc
	 */
	public function end_lvl(&$output, $depth = 0, $arguments = [])
	{
		$link  = $this->links->parent();
		$links = $link->has_parent() ? $link->parent()
		                                    ->children() : $this->root;

		$this->links = $links;
	}

}