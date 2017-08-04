<?php

namespace Twist\Model\Navigation;

use function Twist\view;

/**
 * Class Navigation
 *
 * @package Twist\Model\Navigation
 */
class Navigation
{

    /**
     * Navigation constructor.
     */
    public function __construct()
    {
        add_filter('wp_nav_menu_args', function (array $arguments) {
            $arguments['echo'] = false;

            return $arguments;
        });

        add_filter('pre_wp_nav_menu', function () {
            return $this->render(func_get_arg(1));
        }, 1, 2);
    }

    /**
     * @param mixed  $menu
     * @param string $location
     * @param int    $depth
     *
     * @return string
     */
    public function menu($menu, string $location = null, int $depth = 0): string
    {
        return wp_nav_menu([
            'menu'           => $menu,
            'theme_location' => $location,
            'depth'          => $depth,
        ]);
    }

    /**
     * Render the menu.
     *
     * @param \stdClass $arguments
     *
     * @return string
     */
    protected function render($arguments): string
    {
        $menu = $this->get($arguments->menu, $arguments->theme_location);

        if (empty($menu)) {
            return '';
        }

        $menu  = $this->sort($menu, $arguments);
        $items = $this->items($menu, $arguments);

        return view('partials/menu.twig', ['items' => $items], true);
    }

    /**
     * Get the menu items.
     *
     * @param mixed  $menu
     * @param string $location
     *
     * @return array
     */
    protected function get($menu, string $location = null): array
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
     * @param array     $items
     * @param \stdClass $arguments
     *
     * @return array
     */
    protected function sort(array $items, $arguments): array
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

        return apply_filters('wp_nav_menu_objects', $sortedItems, $arguments);
    }

    /**
     * Get the items collection ready to use in the view.
     *
     * @param array     $items
     * @param \stdClass $arguments
     *
     * @return Items
     */
    protected function items(array $items, $arguments): Items
    {
        $collection = new Items();
        $walker     = new Walker($collection);

        $walker->walk($items, $arguments->depth, $arguments);

        return $collection;
    }

}