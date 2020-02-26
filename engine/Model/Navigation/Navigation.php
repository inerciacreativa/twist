<?php

namespace Twist\Model\Navigation;

use Twist\Library\Hook\Hook;
use Twist\Library\Support\Arr;
use Twist\Model\Link\Links;
use Twist\Model\Taxonomy\Taxonomies;

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
	 * @param int|string|array $menu
	 *
	 * @return Links
	 */
	public static function make($menu): Links
	{
		return (new static())->get($menu);
	}

	/**
	 * Get a navigation menu.
	 *
	 * @param int|string|array $menu
	 *
	 * @return Links
	 */
	public function get($menu): Links
	{
		$id = serialize($menu);
		if (isset(self::$cache[$id])) {
			return self::$cache[$id];
		}

		$arguments = $this->getArguments($menu);
		$items     = $this->getItems($arguments->menu, $arguments->theme_location);

		if (empty($items)) {
			return self::$cache[$id] = new Links();
		}

		$items  = $this->sortItems($items, $arguments);
		$walker = new Walker();

		$walker->walk($items, $arguments->depth, $arguments);

		return self::$cache[$id] = $walker->getLinks();
	}

	/**
	 * Get the complete list of menu arguments.
	 *
	 * @param int|string|array $menu
	 *
	 * @return object
	 */
	protected function getArguments($menu): object
	{
		$defaults = [
			'menu'           => '',
			'depth'          => 0,
			'theme_location' => '',
			'terms_children' => false,
		];

		if (is_array($menu)) {
			$arguments = Arr::defaults($defaults, $menu);
		} else {
			$arguments = array_merge($defaults, ['menu' => $menu]);
		}

		$taxonomies = Taxonomies::getInstance()->getNames();

		if ($arguments['terms_children'] === true) {
			$arguments['terms_children'] = $taxonomies;
		} else if ($arguments['terms_children'] === false) {
			$arguments['terms_children'] = [];
		} else {
			$arguments['terms_children'] = array_intersect($taxonomies, (array) $arguments['terms_children']);
		}

		return (object) $arguments;
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

		// Add the "has-children" class where applicable
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
