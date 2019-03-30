<?php

namespace Twist\Model\Navigation;

use Twist\Library\Hook\Hook;
use Twist\Library\Html\Classes;
use Walker_Nav_Menu;

/**
 * Class Walker
 *
 * @package Twist\Model\Navigation
 */
class Walker extends Walker_Nav_Menu
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
	 */
	public function __construct()
	{
		$this->root = $this->links = new Links();
	}

	/**
	 * @return Links
	 */
	public function getLinks(): Links
	{
		return $this->root;
	}

	/**
	 * @inheritdoc
	 */
	public function start_el(&$output, $item, $depth = 0, $arguments = [], $id = 0): void
	{
		$classes = Classes::make($item->classes)->only(array_keys(static::$classes));
		$classes->set(Hook::apply('nav_menu_css_class', $classes->all(), $item, $arguments, $depth));
		$classes->replace(array_keys(static::$classes), static::$classes);

		$title = Hook::apply('the_title', $item->title, $item->ID);
		$title = Hook::apply('nav_menu_item_title', $title, $item, $arguments, $depth);

		$this->link = new Link([
			'id'      => $item->ID,
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