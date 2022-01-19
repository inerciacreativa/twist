<?php

namespace Twist\Model\Taxonomy;

use Twist\App\AppException;
use Twist\Model\Post\PostsQuery;
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
	 * @var Term|bool|null
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
		$taxonomy = $this->name();
		$id       = $term instanceof Term ? $term->id() : $term;

		switch ($taxonomy) {
			case 'category':
				return PostsQuery::main()->is_category($id);
			case 'post_tag':
				return PostsQuery::main()->is_tag($id);
			default:
				return PostsQuery::main()->is_taxonomy($taxonomy, $id);
		}
	}

	/**
	 * @return Term|null
	 */
	public function current(): ?Term
	{
		if ($this->current === null) {
			try {
				if ($this->is_current(PostsQuery::main()->queried_id())) {
					/** @var WP_Term $term */
					$term = PostsQuery::main()->queried_object();

					$this->current = new Term($term, $this);
				}
			} catch (AppException $exception) {
				$this->current = false;
			}
		}

		return !$this->current ? null : $this->current;
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

		return TermsBuilder::getTerms($this, $terms, $arguments);
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
	 * @return array|string
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
