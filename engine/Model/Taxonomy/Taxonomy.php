<?php

namespace Twist\Model\Taxonomy;

use Twist\App\AppException;
use Twist\Model\Post\Query;
use WP_Taxonomy;
use WP_Term;

/**
 * Class Taxonomy
 *
 * @package Twist\Model\Taxonomy
 */
class Taxonomy implements TaxonomyInterface
{

	/**
	 * @var WP_Taxonomy
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
	 * @throws AppException
	 */
	public function __construct(string $taxonomy)
	{
		if (!taxonomy_exists($taxonomy)) {
			new AppException("The taxonomy '$taxonomy' does not exists");
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
		$id       = $term instanceof Term ? $term->id() : $term;

		if ($function === null) {
			switch ($taxonomy) {
				case 'category':
					$function = static function ($id) {
						return Query::main()->is_category($id);
					};
					break;
				case 'post_tag':
					$function = static function ($id) {
						return Query::main()->is_tag($id);
					};
					break;
				default:
					$function = static function ($id) use ($taxonomy) {
						return Query::main()->is_taxonomy($taxonomy, $id);
					};
			}
		}

		return $function($id);
	}

	/**
	 * @return Term|null
	 */
	public function current(): ?Term
	{
		if ($this->current === null) {
			$this->current = false;

			try {
				if ($this->is_current(Query::main()->queried_id())) {
					/** @var WP_Term $term */
					$term = Query::main()->queried_object();

					$this->current = new Term($term, $this);
				}
			} catch (AppException $exception) {

			}
		}

		return $this->current === false ? null : $this->current;
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

		$arguments['taxonomy']     = $this->taxonomy->name;
		$arguments['hierarchical'] = (bool) $arguments['hierarchical'];

		if (!isset($arguments['pad_counts']) && $arguments['show_count'] && $arguments['hierarchical']) {
			$arguments['pad_counts'] = true;
		}

		if ($arguments['hierarchical'] === true) {
			$exclude = [];

			if ($arguments['exclude_tree']) {
				$exclude = array_merge($exclude, wp_parse_id_list($arguments['exclude_tree']));
			}

			if ($arguments['exclude']) {
				$exclude = array_merge($exclude, wp_parse_id_list($arguments['exclude']));
			}

			$arguments['exclude_tree'] = $exclude;
			$arguments['exclude']      = '';
		}

		$items = get_terms($arguments);

		if (is_array($items)) {
			if (empty($arguments['current_category']) && ($current = $this->current())) {
				$arguments['current_category'] = $current->id();
			}

			if (!$arguments['hierarchical']) {
				$arguments['depth'] = -1;
			}
		}

		return Builder::getTerms($this, $items, $arguments);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->name();
	}

}
