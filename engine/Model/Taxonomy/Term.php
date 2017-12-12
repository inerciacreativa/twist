<?php

namespace Twist\Model\Taxonomy;

use Twist\Model\Model;

/**
 * Class Term
 *
 * @package Twist\Model\Taxonomy
 */
class Term extends Model
{

	/**
	 * @var Taxonomy
	 */
	protected $taxonomy;

	/**
	 * @var \WP_Term
	 */
	protected $term;

	/**
	 * Term constructor.
	 *
	 * @param Taxonomy $taxonomy
	 * @param \WP_Term $term
	 * @param Terms    $terms
	 */
	public function __construct(Taxonomy $taxonomy, \WP_Term $term, Terms $terms = null)
	{
		$this->taxonomy = $taxonomy;
		$this->term     = $term;

		if ($terms && $terms->has_parent()) {
			$this->setParent($terms->parent());
		}
	}

	/**
	 * @return Terms
	 */
	protected function setChildren(): Terms
	{
		return new Terms($this);
	}

	/**
	 * @return int
	 */
	public function id(): int
	{
		return (int) $this->term->term_id;
	}

	/**
	 * @return string
	 */
	public function name(): string
	{
		return $this->sanitize('name');
	}

	/**
	 * @return string
	 */
	public function slug(): string
	{
		return $this->sanitize('slug');
	}

	/**
	 * @return string
	 */
	public function link(): string
	{
		return get_term_link($this->term);
	}

	/**
	 * @return string
	 */
	public function feed(): string
	{
		return get_term_feed_link($this->id(), $this->taxonomy());
	}

	/**
	 * @return string
	 */
	public function description(): string
	{
		return $this->sanitize('description');
	}

	/**
	 * @return int
	 */
	public function count(): int
	{
		return (int) $this->sanitize('count');
	}

	/**
	 * @return Taxonomy
	 */
	public function taxonomy(): Taxonomy
	{
		return $this->taxonomy;
	}

	/**
	 * @return bool
	 */
	public function is_current(): bool
	{
		$current = $this->taxonomy()->current();

		return $current && $current->id() === $this->id();
	}

	/**
	 * @param string $prefix
	 *
	 * @return string
	 */
	public function classes(string $prefix = ''): string
	{
		if ($prefix) {
			$classes = [
				$prefix . '-item',
				$prefix . '-' . $this->taxonomy()->name(),
			];
		} else {
			$prefix  = $this->taxonomy()->name();
			$classes = [
				$prefix,
				$prefix . '-' . $this->term->slug,
			];
		}

		if ($this->is_current()) {
			$classes[] = $prefix . '-current';
		}

		return implode(' ', $classes);
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function sanitize(string $field): string
	{
		return sanitize_term_field($field, $this->term->$field, $this->id(), $this->taxonomy(), 'display');
	}

}