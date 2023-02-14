<?php

namespace Twist\Model\Taxonomy;

use Twist\App\AppException;
use Twist\Library\Html\Classes;
use Walker_Category;
use WP_Term;

/**
 * Class TermsBuilder
 *
 * @package Twist\Model\Taxonomy
 */
class TermsBuilder extends Walker_Category
{

	/**
	 * @var Taxonomy
	 */
	protected $taxonomy;

	/**
	 * @var Terms
	 */
	protected $root;

	/**
	 * @var Terms
	 */
	protected $terms;

	/**
	 * @var Term
	 */
	protected $term;

	/**
	 * @param Taxonomy  $taxonomy
	 * @param WP_Term[] $items
	 * @param array     $arguments
	 *
	 * @return Terms
	 */
	public static function getTerms(Taxonomy $taxonomy, array $items, array $arguments): Terms
	{
		$terms = new Terms();
		if (empty($items)) {
			return $terms;
		}

		$depth   = $taxonomy->is_hierarchical() ? 0 : -1;
		$builder = new static($taxonomy, $terms);
		$builder->walk($items, $depth, $arguments);

		return $terms;
	}

	/**
	 * Builder constructor.
	 *
	 * @param Taxonomy $taxonomy
	 * @param Terms    $terms
	 */
	protected function __construct(Taxonomy $taxonomy, Terms $terms)
	{
		$this->taxonomy = $taxonomy;

		$this->root = $this->terms = $terms;
	}

	/**
	 * @inheritDoc
	 *
	 * @param string  $output    Unused.
	 * @param WP_Term $term      Taxonomy data object.
	 * @param int     $depth     Unused.
	 * @param array   $arguments Optional. An array of arguments.
	 * @param int     $id        Unused.
	 *
	 * @throws AppException
	 */
	public function start_el(&$output, $term, $depth = 0, $arguments = [], $id = 0): void
	{
		$class   = $this->getClasses($term, $arguments);
		$current = $class->has('is-current');

		$this->term = new Term($term, $this->taxonomy, compact('class', 'current'));

		if ($this->terms->has_parent()) {
			$this->term->set_parent($this->terms->parent());
		}

		$this->terms->add($this->term);
	}

	/**
	 * @inheritDoc
	 */
	public function end_el(&$output, $term, $depth = 0, $arguments = []): void
	{
	}

	/**
	 * @inheritDoc
	 */
	public function start_lvl(&$output, $depth = 0, $arguments = []): void
	{
		$this->terms = $this->term->children();
	}

	/**
	 * @inheritDoc
	 *
	 * @noinspection NullPointerExceptionInspection
	 */
	public function end_lvl(&$output, $depth = 0, $arguments = []): void
	{
		$term = $this->terms->parent();

		$this->terms = ($term && $term->has_parent()) ? $term->parent()
												  ->children() : $this->root;
	}

	/**
	 * @param WP_Term $term
	 * @param array   $arguments
	 *
	 * @return Classes
	 */
	private function getClasses(WP_Term $term, array $arguments): Classes
	{
		$classes      = new Classes();
		$currentTerms = $this->getCurrentTerms($arguments);

		foreach ($currentTerms as $current) {
			if ($term->term_id === $current->term_id) {
				$classes[] = 'is-current';
			} else if ($term->term_id === $current->parent) {
				$classes[] = 'is-current-parent';
			} else {
				while ($current->parent) {
					$current = get_term($current->parent, $term->taxonomy);

					if ($term->term_id === $current->parent) {
						$classes = 'is-current-parent';
						break;
					}
				}
			}
		}

		return $classes;
	}

	/**
	 * @param array $arguments
	 *
	 * @return WP_Term[]
	 */
	private function getCurrentTerms(array $arguments): array
	{
		static $terms;

		if (isset($terms)) {
			return $terms;
		}

		$ids = [];

		if (!empty($arguments['current'])) {
			$ids = wp_parse_id_list($arguments['current']);
		} else if ($term = $this->taxonomy->current()) {
			$ids = [$term->id()];
		}

		if (empty($ids)) {
			$terms = [];
		} else {
			$terms = get_terms([
				'taxonomy'   => $this->taxonomy->name(),
				'include'    => $ids,
				'hide_empty' => false,
			]);
		}

		return $terms;
	}

}
