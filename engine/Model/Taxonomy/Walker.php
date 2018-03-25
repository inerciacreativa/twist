<?php

namespace Twist\Model\Taxonomy;

/**
 * Class Walker
 *
 * @package Twist\Model\Taxonomy
 */
class Walker extends \Walker_Category
{

	/**
	 * @var Taxonomy
	 */
	private $taxonomy;

	/**
	 * @var Terms
	 */
	private $root;

	/**
	 * @var Terms
	 */
	private $terms;

	/**
	 * @var Term
	 */
	private $term;

	/**
	 * Walker constructor.
	 *
	 * @param Taxonomy $taxonomy
	 */
	public function __construct(Taxonomy $taxonomy)
	{
		$this->taxonomy = $taxonomy;
		$this->root  = new Terms();
		$this->terms = $this->root;
	}

	/**
	 * @return Terms
	 */
	public function terms(): Terms
	{
		return $this->root;
	}

	/**
	 * @inheritdoc
	 *
	 * @param string   $output    Unused.
	 * @param \WP_Term $term      Taxonomy data object.
	 * @param int      $depth     Unused.
	 * @param array    $arguments Optional. An array of arguments. See
	 *                            wp_list_categories(). Default empty array.
	 * @param int      $id        Unused.
	 */
	public function start_el(&$output, $term, $depth = 0, $arguments = [], $id = 0)
	{
		$this->term = new Term($this->taxonomy, $term, $this->terms);

		$this->terms->add($this->term);
	}

	/**
	 * @inheritdoc
	 *
	 * @param string   $output    Unused.
	 * @param \WP_Term $term      Taxonomy data object.
	 * @param int      $depth     Unused.
	 * @param array    $arguments Optional. An array of arguments. See
	 *                            wp_list_categories(). Default empty array.
	 * @param int      $id        Unused.
	 */
	public function end_el(&$output, $term, $depth = 0, $arguments = [])
	{
	}

	/**
	 * @inheritdoc
	 */
	public function start_lvl(&$output, $depth = 0, $arguments = [])
	{
		$this->terms = $this->term->children();
	}

	/**
	 * @inheritdoc
	 */
	public function end_lvl(&$output, $depth = 0, $arguments = [])
	{
		$term  = $this->terms->parent();
		$terms = $term->has_parent() ? $term->parent()
		                                    ->children() : $this->root;

		$this->terms = $terms;
	}

}
