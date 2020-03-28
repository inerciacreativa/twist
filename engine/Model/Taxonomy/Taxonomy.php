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
	 *
	 * @see \WP_Term_Query::__construct()
	 */
	public function terms(array $options = []): Terms
	{
		$arguments = $this->getArguments('all', $options);

		$items = get_terms($arguments);

		$arguments['depth'] = $arguments['hierarchical'] ? 0 : -1;
		if (is_array($items) && empty($arguments['current_category']) && ($current = $this->current())) {
			$arguments['current_category'] = $current->id();
		}

		return Builder::getTerms($this, $items, $arguments);
	}

	/**
	 * @param array $options
	 *
	 * @return int
	 *
	 * @see \WP_Term_Query::__construct()
	 */
	public function count(array $options = []): int
	{
		$arguments = $this->getArguments('count', $options);

		return (int) get_terms($arguments);
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
	 * @param array  $options
	 *
	 * @return array
	 */
	protected function getArguments(string $fields, array $options): array
	{
		return array_merge([
			'hide_empty' => true,
		], $options, [
			'fields'       => $fields,
			'taxonomy'     => $this->taxonomy->name,
			'hierarchical' => (bool) $this->taxonomy->hierarchical,
		]);
	}

}
