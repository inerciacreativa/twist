<?php

namespace Twist\Model\Taxonomy;

use Twist\App\AppException;
use Walker_Category;
use WP_Term;

/**
 * Class Walker
 *
 * @package Twist\Model\Taxonomy
 */
class Walker extends Walker_Category
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
	 * Walker constructor.
	 *
	 * @param Taxonomy $taxonomy
	 */
	public function __construct(Taxonomy $taxonomy)
	{
		$this->taxonomy = $taxonomy;

		$this->root = $this->terms = new Terms();
	}

	/**
	 * @return Terms
	 */
	public function getTerms(): Terms
	{
		return $this->root;
	}

	/**
	 * @inheritdoc
	 *
	 * @param string  $output     Unused.
	 * @param WP_Term $term       Taxonomy data object.
	 * @param int     $depth      Unused.
	 * @param array   $arguments  Optional. An array of arguments. See
	 *                            wp_list_categories(). Default empty array.
	 * @param int     $id         Unused.
	 *
	 * @throws AppException
	 */
	public function start_el(&$output, $term, $depth = 0, $arguments = [], $id = 0): void
	{
		$this->term = new Term($term, $this->taxonomy);

		if ($this->terms->has_parent()) {
			$this->term->set_parent($this->terms->parent());
		}

		$this->terms->add($this->term);
	}

	/**
	 * @inheritdoc
	 *
	 * @param string  $output     Unused.
	 * @param WP_Term $term       Taxonomy data object.
	 * @param int     $depth      Unused.
	 * @param array   $arguments  Optional. An array of arguments. See
	 *                            wp_list_categories(). Default empty array.
	 * @param int     $id         Unused.
	 */
	public function end_el(&$output, $term, $depth = 0, $arguments = []): void
	{
	}

	/**
	 * @inheritdoc
	 */
	public function start_lvl(&$output, $depth = 0, $arguments = []): void
	{
		$this->terms = $this->term->children();
	}

	/**
	 * @inheritdoc
	 */
	public function end_lvl(&$output, $depth = 0, $arguments = []): void
	{
		$term = $this->terms->parent();

		/** @noinspection NullPointerExceptionInspection */
		$this->terms = $term->has_parent() ? $term->parent()
		                                          ->children() : $this->root;
	}

}
