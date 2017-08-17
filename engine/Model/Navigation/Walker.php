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
	 * @var Items
	 */
	protected $items;

	/**
	 * @var Item
	 */
	protected $item;

	/**
	 * Walker constructor.
	 *
	 * @param Items $items
	 */
	public function __construct(Items $items)
	{
		$this->items = $items;
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
		//$classes = implode(' ', apply_filters('nav_menu_css_class', $classes, $item, $arguments, $depth));
		// Replace class names
		//$classes = str_replace(array_keys(static::$classes), static::$classes, $classes);

		$title = apply_filters('the_title', $item->title, $item->ID);
		$title = apply_filters('nav_menu_item_title', $title, $item, $arguments, $depth);

		$this->item = new Item($this->items, [
			'id'           => $item->ID,
			//'classes' => trim($classes),
			'is_active'    => in_array('current-menu-item', $classes, false),
			'target'       => $item->target,
			'rel'          => $item->xfn,
			'url'          => $item->url,
			'title'        => $title,
		]);

		$this->items->add($this->item);
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
		$this->items = $this->item->children();
	}

	/**
	 * @inheritdoc
	 */
	public function end_lvl(&$output, $depth = 0, $arguments = [])
	{
		$this->items = $this->item->parent()->children();
	}

}