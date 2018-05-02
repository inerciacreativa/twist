<?php

namespace Twist\Model\Taxonomy;

/**
 * Class Taxonomy
 *
 * @package Twist\Model\Taxonomy
 */
class Taxonomy implements TaxonomyInterface
{

	/**
	 * @var \WP_Taxonomy
	 */
	protected $taxonomy;

	/**
	 * @var Term
	 */
	protected $current;

	/**
	 * Taxonomy constructor.
	 *
	 * @param string $taxonomy
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $taxonomy)
	{
		if (!taxonomy_exists($taxonomy)) {
			throw new \InvalidArgumentException("The taxonomy '$taxonomy' does not exists");
		}

		$this->taxonomy = get_taxonomy($taxonomy);
	}

	/**
	 * @return string
	 */
	public function name(): string
	{
		return $this->taxonomy->name;
	}

	/**
	 * @return bool
	 */
	public function is_hierarchical(): bool
	{
		return $this->taxonomy->hierarchical;
	}

	/**
	 * @param int|string|array $term
	 *
	 * @return bool
	 */
	public function is_current($term = null): bool
	{
		static $function;

		$taxonomy = $this->name();

		if ($function === null) {
			if ($taxonomy === 'category') {
				$function = function ($term) {
					return is_category($term);
				};
			} elseif ($taxonomy === 'post_tag') {
				$function = function ($term) {
					return is_tag($term);
				};
			} else {
				$function = function ($term) use ($taxonomy) {
					return is_tax($taxonomy, $term);
				};
			}
		}

		return $function($term);
	}

	/**
	 * @return Term|null
	 */
	public function current()
	{
		if ($this->current === null) {
			$this->current = false;

			if ($this->is_current(get_queried_object_id())) {
				$this->current = new Term($this, get_queried_object());
			}
		}

		return $this->current ?? null;
	}

	/**
	 * @param array $options
	 *
	 * @return Terms
	 */
	public function terms(array $options = []): Terms
	{
		$arguments = array_merge([
			'child_of'         => 0,
			'current_category' => 0,
			'depth'            => 0,
			'exclude'          => '',
			'exclude_tree'     => '',
			'hide_empty'       => 1,
			'hierarchical'     => $this->taxonomy->hierarchical,
			'order'            => 'ASC',
			'orderby'          => 'name',
			'show_count'       => 0,
		], $options);

		$arguments['taxonomy'] = $this->taxonomy->name;

		if (!isset($arguments['pad_counts']) && $arguments['show_count'] && $arguments['hierarchical']) {
			$arguments['pad_counts'] = true;
		}

		if ((bool) $arguments['hierarchical'] === true) {
			$exclude_tree = [];

			if ($arguments['exclude_tree']) {
				$exclude_tree = array_merge($exclude_tree, wp_parse_id_list($arguments['exclude_tree']));
			}

			if ($arguments['exclude']) {
				$exclude_tree = array_merge($exclude_tree, wp_parse_id_list($arguments['exclude']));
			}

			$arguments['exclude_tree'] = $exclude_tree;
			$arguments['exclude']      = '';
		}

		$walker  = new Walker($this);
		$objects = get_terms($arguments);

		if ($objects) {
			$arguments['walker'] = $walker;

			if (empty($arguments['current_category']) && $this->is_current()) {
				$arguments['current_category'] = $this->current()->id();
			}

			if ($arguments['hierarchical']) {
				$depth = $arguments['depth'];
			} else {
				$depth = -1;
			}

			walk_category_tree($objects, $depth, $arguments);
		}

		return $walker->terms();
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->name();
	}

}