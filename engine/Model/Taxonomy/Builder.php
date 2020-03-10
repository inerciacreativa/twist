<?php

namespace Twist\Model\Taxonomy;

use Twist\App\AppException;
use Walker_Category;
use WP_Term;

/**
 * Class Builder
 *
 * @package Twist\Model\Taxonomy
 */
class Builder extends Walker_Category
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

		$builder = new static($taxonomy, $terms);
		$builder->walk($items, $arguments['depth'], $arguments);

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
		$this->term = new Term($term, $this->taxonomy);

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

		$this->terms = $term->has_parent() ? $term->parent()
												  ->children() : $this->root;
	}

}
