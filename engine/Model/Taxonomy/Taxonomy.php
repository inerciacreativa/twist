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
	 * @return array
	 */
	public function post_types(): array
	{
		return $this->taxonomy->object_type;
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
		static $current;

		if ($current === null) {
			try {
				if ($this->is_current(Query::main()->queried_id())) {
					/** @var WP_Term $term */
					$term = Query::main()->queried_object();

					$current = new Term($term, $this);
				}
			} catch (AppException $exception) {
				$current = false;
			}
		}

		return !$current ? null : $current;
	}

	/**
	 * Retrieve a Collection of Term. The 'current_category' argument used in wp_list_categories()
	 * has been renamed to current.
	 *
	 * @param array $arguments
	 *
	 * @return Terms
	 *
	 * @see \WP_Term_Query::__construct()
	 * @see wp_list_categories()
	 */
	public function terms(array $arguments = []): Terms
	{
		$terms = $this->getTerms('all', $arguments);

		return Builder::getTerms($this, $terms, $arguments);
	}

	/**
	 * @param array $arguments
	 *
	 * @return int
	 *
	 * @see \WP_Term_Query::__construct()
	 */
	public function count(array $arguments = []): int
	{
		return (int) $this->getTerms('count', $arguments);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->name();
	}

	/**
	 * @param string $fields
	 * @param array  $arguments
	 *
	 * @return array|int
	 */
	protected function getTerms(string $fields, array $arguments)
	{
		$arguments = array_merge([
			'hide_empty' => true,
		], $arguments, [
			'fields'       => $fields,
			'taxonomy'     => $this->taxonomy->name,
			'hierarchical' => (bool) $this->taxonomy->hierarchical,
		]);

		return get_terms($arguments);
	}

}
