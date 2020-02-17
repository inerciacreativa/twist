<?php

namespace Twist\Model\Navigation;

use Twist\Library\Hook\Hook;
use Twist\Model\Link\Links;

/**
 * Class Navigation
 *
 * @package Twist\Model\Navigation
 */
class Navigation
{

	/**
	 * @var Links[]
	 */
	private static $cache = [];

	/**
	 * Get a navigation menu.
	 *
	 * @param int|string $menu
	 * @param string     $location
	 * @param int        $depth
	 *
	 * @return Links
	 */
	public static function make($menu, string $location = '', int $depth = 0): Links
	{
		return (new static())->get($menu, $location, $depth);
	}

	/**
	 * Get a navigation menu.
	 *
	 * @param int|string $menu
	 * @param string     $location
	 * @param int        $depth
	 *
	 * @return Links
	 */
	public function get($menu, string $location = '', int $depth = 0): Links
	{
		$id = sprintf('%s-%s-%d', $menu, $location, $depth);
		if (isset(self::$cache[$id])) {
			return self::$cache[$id];
		}

		$items = $this->getItems($menu, $location);

		if (empty($items)) {
			return self::$cache[$id] = new Links();
		}

		$arguments = $this->getArguments($menu, $location, $depth);
		$items     = $this->sortItems($items, $arguments);
		$walker    = new Walker();

		$walker->walk($items, $arguments->depth, $arguments);

		return self::$cache[$id] = $walker->getLinks();
	}

	/**
	 * Get the complete list of menu arguments.
	 *
	 * @param int|string $menu
	 * @param string     $location
	 * @param int        $depth
	 *
	 * @return object
	 */
	protected function getArguments($menu, string $location, int $depth): object
	{
		return (object) [
			'menu'            => $menu,
			'container'       => 'div',
			'container_class' => '',
			'container_id'    => '',
			'menu_class'      => 'menu',
			'menu_id'         => '',
			'echo'            => false,
			'fallback_cb'     => false,
			'before'          => '',
			'after'           => '',
			'link_before'     => '',
			'link_after'      => '',
			'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
			'item_spacing'    => 'preserve',
			'depth'           => $depth,
			'walker'          => '',
			'theme_location'  => $location,
		];
	}

	/**
	 * Get the menu items.
	 *
	 * @param mixed  $menu
	 * @param string $location
	 *
	 * @return array
	 */
	protected function getItems($menu, string $location = null): array
	{
		$menu  = wp_get_nav_menu_object($menu);
		$items = false;

		if (!$menu && $location && ($locations = get_nav_menu_locations()) && isset($locations[$location])) {
			$menu = wp_get_nav_menu_object($locations[$location]);
		}

		// get the first menu that has items if we still can't find a menu
		if (!$menu && !$location) {
			$menus = wp_get_nav_menus();

			foreach ($menus as $maybeMenu) {
				if ($items = wp_get_nav_menu_items($maybeMenu->term_id, ['update_post_term_cache' => false])) {
					$menu = $maybeMenu;
					break;
				}
			}
		}

		// If the menu exists, get its items.
		if (!$items && $menu && !is_wp_error($menu)) {
			$items = wp_get_nav_menu_items($menu->term_id, ['update_post_term_cache' => false]);
		}

		if (!$menu || is_wp_error($menu)) {
			return [];
		}

		return $items;
	}

	/**
	 * Sort the menu items and decorates each item with its properties.
	 *
	 * @param array  $items
	 * @param object $arguments
	 *
	 * @return array
	 */
	protected function sortItems(array $items, object $arguments): array
	{
		_wp_menu_item_classes_by_context($items);

		$sortedItems = $itemsWithChildren = [];
		foreach ($items as $item) {
			$sortedItems[$item->menu_order] = $item;

			if ($item->menu_item_parent) {
				$itemsWithChildren[$item->menu_item_parent] = true;
			}
		}

		// Add the menu-item-has-children class where applicable
		if ($itemsWithChildren) {
			foreach ($sortedItems as &$item) {
				if (isset($itemsWithChildren[$item->ID])) {
					$item->classes[] = 'has-children';
				}
			}
		}

		return Hook::apply('wp_nav_menu_objects', $sortedItems, $arguments);
	}

}
